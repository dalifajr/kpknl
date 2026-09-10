<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignment;
use App\Models\Comment;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all'); // default to 'all'
        $year = $request->query('year');
        $search = $request->query('search');
        $userId = $request->query('user_id');

        $query = TaskAssignment::with('task', 'user', 'submission');

        // Filter by Status Tab
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by Year
        if ($year) {
            $query->whereYear('created_at', $year);
        }

        // Filter by User PIC
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Filter by Search (User name or Task title)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })->orWhereHas('task', function ($tq) use ($search) {
                    $tq->where('title', 'like', "%{$search}%");
                });
            });
        }

        $perPage = $request->query('per_page', 10);
        $assignments = $query->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();
        
        $users = \App\Models\User::where('role', 'user')->orderBy('name', 'asc')->get();

        return view('reviews.index', compact('assignments', 'status', 'year', 'search', 'users', 'userId'));
    }

    public function show(TaskAssignment $assignment)
    {
        if (in_array($assignment->status, ['pending', 'draft'])) {
            return redirect()->route('user-tasks.show', $assignment->id);
        }

        $assignment->load('task', 'user', 'submission', 'comments.user');
        return view('reviews.show', compact('assignment'));
    }

    public function process(Request $request, TaskAssignment $assignment, \App\Services\NotificationService $notificationService)
    {
        $request->validate([
            'action' => 'required|in:acc,revisi,batal_acc,cancel_submission',
            'comment' => 'nullable|string'
        ]);

        $user = auth()->user();

        // 1. Process ACC
        if ($request->action === 'acc') {
            if ($assignment->status === 'acc') {
                if ($request->ajax()) return response()->json(['message' => 'Laporan sudah berstatus ACC.', 'errors' => ['error' => ['Laporan sudah berstatus ACC.']]], 400);
                return redirect()->back()->withErrors(['error' => 'Laporan sudah berstatus ACC.']);
            }
            $assignment->update(['status' => 'acc', 'reviewed_by' => $user->id]);
            
            $notificationService->send(
                $assignment->user,
                'Laporan ACC',
                'Laporan Anda untuk tugas ' . $assignment->task->title . ' telah di-ACC.',
                config('app.url') . '/user-tasks/' . $assignment->id
            );
            
            $msg = 'Laporan berhasil di-ACC.';
        } 
        
        // 2. Process Revisi
        elseif ($request->action === 'revisi') {
            if ($assignment->status === 'acc' && $user->role !== 'superadmin') {
                if ($request->ajax()) return response()->json(['message' => 'ACC bersifat final untuk Admin.', 'errors' => ['error' => ['ACC bersifat final untuk Admin. Hanya Superadmin yang dapat merevisi laporan yang sudah di-ACC.']]], 400);
                return redirect()->back()->withErrors(['error' => 'ACC bersifat final untuk Admin. Hanya Superadmin yang dapat merevisi laporan yang sudah di-ACC.']);
            }
            if (empty($request->comment)) {
                if ($request->ajax()) return response()->json(['message' => 'Komentar wajib diisi jika status Revisi.', 'errors' => ['error' => ['Komentar wajib diisi jika status Revisi.']]], 400);
                return redirect()->back()->withErrors(['error' => 'Komentar wajib diisi jika status Revisi.']);
            }
            $assignment->update(['status' => 'revisi', 'reviewed_by' => $user->id]);
            
            $notificationService->send(
                $assignment->user,
                'Revisi Laporan',
                'Laporan Anda untuk tugas ' . $assignment->task->title . ' perlu direvisi.',
                config('app.url') . '/user-tasks/' . $assignment->id
            );

            $msg = 'Laporan dikembalikan untuk direvisi.';
        }

        // 3. Process Batal ACC (Only Superadmin)
        elseif ($request->action === 'batal_acc') {
            if ($user->role !== 'superadmin') {
                if ($request->ajax()) return response()->json(['message' => 'Hanya Superadmin yang dapat membatalkan ACC.', 'errors' => ['error' => ['Hanya Superadmin yang dapat membatalkan ACC.']]], 403);
                return redirect()->back()->withErrors(['error' => 'Hanya Superadmin yang dapat membatalkan ACC.']);
            }
            $assignment->update(['status' => 'submitted', 'reviewed_by' => null]);
            $msg = 'Status ACC berhasil dibatalkan.';
        }

        // 4. Process Cancel Submission (Only Superadmin)
        elseif ($request->action === 'cancel_submission') {
            if ($user->role !== 'superadmin') {
                if ($request->ajax()) return response()->json(['message' => 'Hanya Superadmin yang dapat membatalkan pengisian.', 'errors' => ['error' => ['Hanya Superadmin yang dapat membatalkan pengisian.']]], 403);
                return redirect()->back()->withErrors(['error' => 'Hanya Superadmin yang dapat membatalkan pengisian.']);
            }
            if ($assignment->status === 'acc') {
                if ($request->ajax()) return response()->json(['message' => 'Batalkan status ACC terlebih dahulu sebelum membatalkan pengisian.', 'errors' => ['error' => ['Batalkan status ACC terlebih dahulu sebelum membatalkan pengisian.']]], 400);
                return redirect()->back()->withErrors(['error' => 'Batalkan status ACC terlebih dahulu sebelum membatalkan pengisian.']);
            }
            
            if ($assignment->submission) {
                $assignment->submission->delete();
            }
            
            $assignment->update(['status' => 'pending', 'reviewed_by' => null]);
            
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Pengisian berhasil dibatalkan. Status tugas kembali pending.', 'redirect' => route('reviews.index')]);
            }
            return redirect()->route('reviews.index')->with('success', 'Pengisian berhasil dibatalkan. Status tugas kembali pending.');
        }

        // Add comment if provided
        if (!empty($request->comment)) {
            Comment::create([
                'task_assignment_id' => $assignment->id,
                'user_id' => $user->id,
                'body' => $request->comment,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'redirect' => route('reviews.show', $assignment->id)
            ]);
        }

        return redirect()->route('reviews.show', $assignment->id)->with('success', $msg);
    }
}
