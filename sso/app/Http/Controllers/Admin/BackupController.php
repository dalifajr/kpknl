<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $backups = BackupService::getBackupList();
        return view('admin.backup.index', compact('backups'));
    }

    public function create()
    {
        try {
            $filename = BackupService::createBackup();
            return redirect()->route('admin.backup.index')->with('success', "Backup database '{$filename}' berhasil dibuat.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $path = 'backups/' . basename($filename);

        if (!Storage::disk('local')->exists($path)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        return Storage::disk('local')->download($path);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt|max:50000',
        ], [
            'backup_file.required' => 'Pilih file backup database (.sql).',
            'backup_file.mimes' => 'File harus berformat .sql atau .txt.',
        ]);

        try {
            $file = $request->file('backup_file');
            if (!$file || !$file->isValid()) {
                throw new \Exception('File backup tidak valid atau gagal diunggah.');
            }

            // Gunakan getPathname() sebagai fallback jika getRealPath() kosong
            $path = $file->getRealPath() ?: $file->getPathname();
            
            if (empty($path)) {
                throw new \Exception('Path temporary file tidak ditemukan.');
            }

            $sqlContent = file_get_contents($path);
            
            if ($sqlContent === false) {
                throw new \Exception('Gagal membaca isi file backup.');
            }

            BackupService::restoreBackup($sqlContent);

            return redirect()->route('admin.backup.index')->with('success', 'Database berhasil di-restore dari file SQL.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal melakukan restore: ' . $e->getMessage());
        }
    }

    public function destroy(string $filename)
    {
        $path = 'backups/' . basename($filename);

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            return back()->with('success', "File backup '{$filename}' berhasil dihapus.");
        }

        return back()->with('error', 'File backup tidak ditemukan.');
    }
}
