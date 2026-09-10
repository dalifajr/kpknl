<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignment;
use App\Models\TaskSubmission;
use App\Models\Comment;
use Illuminate\Http\Request;

class UserTaskController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $year = $request->query('year');
        $search = $request->query('search');
        $pic_id = $request->query('pic_id');
        $period_type = $request->query('period_type');
        
        $query = TaskAssignment::with('task', 'user');
        
        if (auth()->user()->role === 'user') {
            $query->where('user_id', auth()->id());
        } else {
            // Admin/Superadmin can filter by PIC
            if ($pic_id) {
                $query->where('user_id', $pic_id);
            }
        }
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($year) {
            $query->whereYear('created_at', $year);
        }

        if ($period_type) {
            $query->whereHas('task', function ($q) use ($period_type) {
                $q->where('period_type', $period_type);
            });
        }

        if ($search) {
            $query->whereHas('task', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $perPage = $request->query('per_page', 10);
        $assignments = $query->orderBy('deadline_date', 'asc')->paginate($perPage)->withQueryString();
        
        $users = [];
        if (in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            $users = \App\Models\User::where('role', 'user')->orderBy('name')->get();
        }

        return view('user-tasks.index', compact('assignments', 'status', 'year', 'search', 'users', 'pic_id', 'period_type'));
    }

    public function show(TaskAssignment $assignment)
    {
        $user = auth()->user();
        if ($assignment->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        if (in_array($user->role, ['admin', 'superadmin']) && in_array($assignment->status, ['submitted', 'acc', 'revisi'])) {
            return redirect()->route('reviews.show', $assignment->id);
        }

        if ($assignment->open_date && \Carbon\Carbon::parse($assignment->open_date)->isFuture()) {
            if ($user->role === 'user') {
                return redirect()->route('user-tasks.index')->with('error', 'Tugas ini belum dibuka untuk pengisian.');
            }
        }

        $assignment->load('task', 'submission', 'comments.user');
        return view('user-tasks.show', compact('assignment'));
    }

    public function submit(Request $request, TaskAssignment $assignment, \App\Services\NotificationService $notificationService)
    {
        $user = auth()->user();
        if ($assignment->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        $isSubmit = $request->action === 'submit';

        $request->validate([
            'nomor_surat' => ($isSubmit ? 'required' : 'nullable') . '|string|max:255',
            'tanggal_surat' => ($isSubmit ? 'required' : 'nullable') . '|date',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'action' => 'required|in:draft,submit'
        ]);

        $submissionData = $request->only('nomor_surat', 'tanggal_surat', 'notes');
        $submissionData['submitted_by'] = $user->id;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            if (!$file->isValid()) {
                return redirect()->back()->withErrors(['attachment' => 'File lampiran tidak valid atau ukurannya terlalu besar.'])->withInput();
            }
            
            // Bypass Laravel's store() which relies on getRealPath() that sometimes fails on Windows Temp
            $filename = \Illuminate\Support\Str::random(40) . '.' . $file->getClientOriginalExtension();
            $path = 'attachments/' . $filename;
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, file_get_contents($file->getPathname()));
            
            $submissionData['attachment_path'] = $path;
        }

        if ($assignment->submission) {
            $assignment->submission->update($submissionData);
        } else {
            $assignment->submission()->create($submissionData);
        }

        $isTakeover = ($user->id !== $assignment->user_id);

        if ($request->action === 'draft') {
            $assignment->update(['status' => 'draft']);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Laporan berhasil disimpan sebagai draft.',
                    'redirect' => back()->getTargetUrl()
                ]);
            }
            return redirect()->back()->with('success', 'Laporan berhasil disimpan sebagai draft.');
        } else {
            if ($isTakeover) {
                // Admin takes over -> automatically ACC
                $assignment->update([
                    'status' => 'acc',
                    'reviewed_by' => $user->id
                ]);
                
                // Notify the PIC
                $notificationService->send(
                    $assignment->user,
                    'Tugas Diambil Alih',
                    'Tugas ' . $assignment->task->title . ' telah diisi dan diselesaikan oleh Admin ' . $user->name,
                    config('app.url') . '/user-tasks/' . $assignment->id
                );
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Laporan telah berhasil di-submit (Takeover) dan statusnya Selesai (ACC).',
                        'redirect' => route('tasks.index')
                    ]);
                }
                return redirect()->route('tasks.index')->with('success', 'Laporan telah berhasil di-submit (Takeover) dan statusnya Selesai (ACC).');
            } else {
                $assignment->update(['status' => 'submitted']);
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Laporan berhasil disubmit ke admin.',
                        'redirect' => route('user-tasks.index')
                    ]);
                }
                
                // Notification logic for normal PIC submission
                $admins = \App\Models\User::whereIn('role', ['admin', 'superadmin'])->get();
                foreach ($admins as $admin) {
                    $notificationService->send(
                        $admin,
                        'Laporan Baru Disubmit',
                        $assignment->user->name . ' telah men-submit laporan untuk ' . $assignment->task->title,
                        config('app.url') . '/reviews/' . $assignment->id
                    );
                }
                return redirect()->route('user-tasks.index')->with('success', 'Laporan berhasil di-submit dan menunggu verifikasi.');
            }
        }
    }

    public function addComment(Request $request, TaskAssignment $assignment)
    {
        $user = auth()->user();
        if ($assignment->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        $request->validate(['body' => 'required|string']);

        Comment::create([
            'task_assignment_id' => $assignment->id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil ditambahkan.',
                'redirect' => back()->getTargetUrl()
            ]);
        }

        return redirect()->back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function deleteDraft(Request $request, TaskAssignment $assignment)
    {
        $user = auth()->user();
        


        if ($assignment->user_id !== $user->id && !in_array($user->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        if ($assignment->status !== 'draft') {
            if ($request->ajax()) {
                return response()->json(['message' => 'Hanya status draft yang bisa dibatalkan.', 'errors' => ['error' => ['Hanya status draft yang bisa dibatalkan.']]], 400);
            }
            return redirect()->back()->with('error', 'Hanya status draft yang bisa dibatalkan.');
        }

        if ($assignment->submission) {
            $assignment->submission->delete();
        }
        
        // Check if parent task is soft-deleted
        $task = $assignment->task()->withTrashed()->first();
        
        if ($task && $task->trashed()) {
            $assignment->delete(); // Delete task assignment entirely
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengisian dibatalkan dan tugas dihapus dari daftar Anda karena tugas induk telah dihapus.',
                    'redirect' => route('user-tasks.index')
                ]);
            }
            return redirect()->route('user-tasks.index')->with('success', 'Pengisian dibatalkan dan tugas dihapus dari daftar Anda karena tugas induk telah dihapus oleh Admin.');
        } else {
            $assignment->update(['status' => 'pending']);
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pengisian berhasil dibatalkan.',
                    'redirect' => back()->getTargetUrl()
                ]);
            }
            return redirect()->back()->with('success', 'Pengisian berhasil dibatalkan.');
        }
    }

    public function destroy(Request $request, TaskAssignment $assignment)
    {
        $user = auth()->user();
        if ($user->role !== 'superadmin') {
            abort(403, 'Hanya superadmin yang dapat menghapus penugasan ini.');
        }

        if (in_array($assignment->status, ['acc'])) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Tugas yang sudah ACC tidak dapat dihapus. Batalkan ACC terlebih dahulu.', 'errors' => ['error' => ['Tugas yang sudah ACC tidak dapat dihapus. Batalkan ACC terlebih dahulu.']]], 400);
            }
            return redirect()->back()->with('error', 'Tugas yang sudah ACC tidak dapat dihapus.');
        }

        $assignment->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dihapus secara permanen (unassign).',
                'redirect' => route('reviews.index')
            ]);
        }
        return redirect()->route('reviews.index')->with('success', 'Tugas berhasil dihapus secara permanen.');
    }
}
