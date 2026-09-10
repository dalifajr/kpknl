<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationBackup;
use App\Models\ApplicationDeployment;
use App\Services\MaintenanceOrchestratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaintenanceOrchestratorController extends Controller
{
    /**
     * Display orchestrator fleet hub.
     */
    public function index(Request $request)
    {
        $applications = Application::withCount(['deployments', 'backups'])
            ->with(['deployments' => fn($q) => $q->latest()->limit(1)])
            ->get();

        $kpis = [
            'total_apps' => $applications->count(),
            'online_apps' => $applications->where('maintenance_mode', false)->count(),
            'maintenance_apps' => $applications->where('maintenance_mode', true)->count(),
            'total_backups' => ApplicationBackup::count(),
            'total_deployments' => ApplicationDeployment::count(),
            'last_deployment' => ApplicationDeployment::with('application')->latest()->first(),
        ];

        // Recent 5 deployments for hub summary
        $recentDeployments = ApplicationDeployment::with('application', 'deployer')
            ->latest()
            ->limit(5)
            ->get();

        // Recent 5 backups for hub summary
        $recentBackups = ApplicationBackup::with('application', 'creator')
            ->latest()
            ->limit(5)
            ->get();

        $gitRemoteUrl = 'https://github.com/dalifajr/kpknl.git';

        return view('admin.maintenance.index', compact(
            'applications',
            'kpis',
            'recentDeployments',
            'recentBackups',
            'gitRemoteUrl'
        ));
    }

    /**
     * Dedicated Application Workspace / Console.
     */
    public function appConsole(Application $application)
    {
        $application->load([
            'deployments' => fn($q) => $q->with('deployer', 'backup')->latest()->limit(10),
            'backups' => fn($q) => $q->with('creator')->latest()->limit(10),
            'releases' => fn($q) => $q->latest()->limit(10),
        ]);

        $recentCommits = MaintenanceOrchestratorService::getCommitHistory($application, 15);
        $gitRemoteUrl = 'https://github.com/dalifajr/kpknl.git';

        return view('admin.maintenance.app_console', compact(
            'application',
            'recentCommits',
            'gitRemoteUrl'
        ));
    }

    /**
     * Dedicated Global Deployments Audit Trail Page.
     */
    public function deployments(Request $request)
    {
        $applications = Application::all(['id', 'name', 'slug']);
        
        $query = ApplicationDeployment::with('application', 'deployer', 'backup')->latest();

        if ($request->filled('app_id')) {
            $query->where('application_id', $request->input('app_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('deployment_type', $request->input('type'));
        }

        $deployments = $query->paginate(15)->withQueryString();

        return view('admin.maintenance.deployments', compact(
            'deployments',
            'applications'
        ));
    }

    /**
     * Dedicated Global Database Snapshots Vault Page.
     */
    public function backups(Request $request)
    {
        $applications = Application::all(['id', 'name', 'slug', 'database_name']);

        $query = ApplicationBackup::with('application', 'creator')->latest();

        if ($request->filled('app_id')) {
            $query->where('application_id', $request->input('app_id'));
        }

        if ($request->filled('type')) {
            $query->where('backup_type', $request->input('type'));
        }

        $backups = $query->paginate(15)->withQueryString();

        return view('admin.maintenance.backups', compact(
            'backups',
            'applications'
        ));
    }

    /**
     * Fetch remote updates from GitHub repository.
     */
    public function fetchGit()
    {
        try {
            $res = MaintenanceOrchestratorService::fetchRemote('main');
            if ($res['success']) {
                return back()->with('success', 'Berhasil memperbarui katalog git dari remote repository (origin/main).');
            }
            return back()->with('warning', 'Git fetch selesai dengan pesan: ' . $res['output']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal melakukan git fetch: ' . $e->getMessage());
        }
    }

    /**
     * Get commit history as JSON for dynamic app switching.
     */
    public function getGitCommits(Application $application)
    {
        $commits = MaintenanceOrchestratorService::getCommitHistory($application, 15);
        return response()->json([
            'success' => true,
            'application' => [
                'id' => $application->id,
                'name' => $application->name,
                'current_commit' => $application->current_commit,
                'current_version' => $application->current_version,
            ],
            'commits' => $commits,
        ]);
    }

    /**
     * Execute 7-stage deployment pipeline (update/deploy).
     */
    public function deploy(Request $request, Application $application)
    {
        $request->validate([
            'target_commit' => 'required|string|size:40',
            'deployment_type' => 'nullable|in:update,hotfix,downgrade',
        ], [
            'target_commit.required' => 'Pilih target commit hash yang valid.',
            'target_commit.size' => 'Commit hash harus berupa 40 karakter SHA.',
        ]);

        $targetCommit = $request->input('target_commit');
        $type = $request->input('deployment_type', 'update');

        try {
            $deployment = MaintenanceOrchestratorService::executeDeploy(
                $application,
                $targetCommit,
                $type,
                auth()->user()
            );

            if ($deployment->status === 'success') {
                return redirect()->route('admin.maintenance.index', ['app_id' => $application->id])
                    ->with('success', "Deployment aplikasi {$application->name} berhasil diselesaikan melalui 7-stage pipeline.");
            } elseif ($deployment->status === 'rolled_back') {
                return redirect()->route('admin.maintenance.index', ['app_id' => $application->id])
                    ->with('error', "Deployment gagal dan telah di-rollback secara otomatis ke kondisi aman. Silakan periksa log pipeline.");
            } else {
                return redirect()->route('admin.maintenance.index', ['app_id' => $application->id])
                    ->with('warning', "Deployment selesai dengan status: {$deployment->status}.");
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat deployment: ' . $e->getMessage());
        }
    }

    /**
     * Execute rollback/downgrade to previous deployment.
     */
    public function rollback(Request $request, Application $application)
    {
        $request->validate([
            'deployment_id' => 'required|exists:application_deployments,id',
        ], [
            'deployment_id.required' => 'Pilih deployment yang akan dijadikan target rollback.',
        ]);

        try {
            $deployment = MaintenanceOrchestratorService::executeRollback(
                $application,
                (int) $request->input('deployment_id'),
                auth()->user()
            );

            if ($deployment->status === 'success') {
                return redirect()->route('admin.maintenance.index', ['app_id' => $application->id])
                    ->with('success', "Aplikasi {$application->name} berhasil di-rollback/downgrade dengan aman.");
            }
            return redirect()->route('admin.maintenance.index', ['app_id' => $application->id])
                ->with('error', "Gagal melakukan rollback: Silakan periksa log.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat rollback: ' . $e->getMessage());
        }
    }

    /**
     * Toggle maintenance mode for application.
     */
    public function toggleMaintenance(Request $request, Application $application)
    {
        try {
            if ($application->maintenance_mode) {
                MaintenanceOrchestratorService::disableMaintenanceMode($application, auth()->user());
                return back()->with('success', "Mode pemeliharaan untuk {$application->name} telah dinonaktifkan. Aplikasi kembali ONLINE.");
            } else {
                $secret = $request->input('bypass_token') ?: null;
                $res = MaintenanceOrchestratorService::enableMaintenanceMode($application, $secret, auth()->user());
                return back()->with('success', "Mode pemeliharaan untuk {$application->name} diaktifkan. Token bypass rahasia: {$res['bypass_token']}");
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengubah status pemeliharaan: ' . $e->getMessage());
        }
    }

    /**
     * Create manual database snapshot.
     */
    public function createBackup(Request $request, Application $application)
    {
        $request->validate([
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $backup = MaintenanceOrchestratorService::createDatabaseSnapshot(
                $application,
                'manual',
                $request->input('notes', 'Snapshot manual dibuat oleh superadmin'),
                auth()->user()
            );

            return back()->with('success', "Snapshot database '{$backup->filename}' ({$backup->formatted_size}) berhasil dibuat.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat snapshot database: ' . $e->getMessage());
        }
    }

    /**
     * Restore database snapshot.
     */
    public function restoreBackup(ApplicationBackup $backup)
    {
        try {
            $res = MaintenanceOrchestratorService::restoreDatabaseSnapshot($backup, auth()->user());
            if ($res['success']) {
                return back()->with('success', "Snapshot '{$backup->filename}' berhasil di-restore ke database {$backup->application->database_name}.");
            }
            return back()->with('error', $res['message']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal restore snapshot: ' . $e->getMessage());
        }
    }

    /**
     * Download database snapshot SQL file.
     */
    public function downloadBackup(ApplicationBackup $backup)
    {
        $fullPath = storage_path("app/{$backup->db_dump_path}");
        if (!file_exists($fullPath)) {
            return back()->with('error', 'File snapshot tidak ditemukan di storage.');
        }

        return response()->download($fullPath, $backup->filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Get deployment pipeline log details as JSON.
     */
    public function getDeploymentLog(ApplicationDeployment $deployment)
    {
        return response()->json([
            'id' => $deployment->id,
            'app_name' => $deployment->application->name,
            'type' => $deployment->deployment_type,
            'source_commit' => $deployment->source_commit,
            'target_commit' => $deployment->target_commit,
            'status' => $deployment->status,
            'current_stage' => $deployment->current_stage,
            'logs' => $deployment->pipeline_logs,
            'started_at' => $deployment->started_at?->format('Y-m-d H:i:s'),
            'finished_at' => $deployment->finished_at?->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Check health status of application via AJAX.
     */
    public function checkHealth(Application $application)
    {
        $health = MaintenanceOrchestratorService::checkAppHealth($application);
        return response()->json($health);
    }
}
