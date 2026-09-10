<?php

namespace App\Http\Controllers;

use App\Models\TaskAssignment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $year = $request->query('year');
        $pic_id = $request->query('pic_id');

        $applyFilters = function($query) use ($year, $pic_id, $user) {
            if ($year) {
                $query->whereYear('created_at', $year);
            }
            if ($user->role === 'user') {
                $query->where('user_id', $user->id);
            } else {
                if ($pic_id) {
                    $query->where('user_id', $pic_id);
                }
            }
            return $query;
        };

        // User PIC Dashboard Data
        $pendingTasksQuery = TaskAssignment::whereIn('status', ['pending', 'draft', 'revisi']);
        $pendingTasks = $applyFilters($pendingTasksQuery)->count();

        // Additional counts for User PIC (and admin sees all)
        $submittedTasksQuery = TaskAssignment::where('status', 'submitted');
        $accTasksQuery = TaskAssignment::where('status', 'acc');
        $revisiTasksQuery = TaskAssignment::where('status', 'revisi');

        $submittedTasks = $applyFilters($submittedTasksQuery)->count();
        $accTasks = $applyFilters($accTasksQuery)->count();
        $revisiTasks = $applyFilters($revisiTasksQuery)->count();

        // Admin/Superadmin Dashboard Data
        $waitingReviews = 0;
        $stats = [];
        $picStats = [];
        $users = [];
        
        if (in_array($user->role, ['superadmin', 'admin'])) {
            $query = TaskAssignment::where('status', 'submitted');
            $waitingReviews = $applyFilters($query)->count();

            // Stats for Donut Chart
            $statsQuery = TaskAssignment::selectRaw('status, count(*) as total');
            $statsQuery = $applyFilters($statsQuery);

            $statsRaw = $statsQuery->groupBy('status')->pluck('total', 'status')->toArray();
            
            $stats = [
                'pending' => $statsRaw['pending'] ?? 0,
                'draft' => $statsRaw['draft'] ?? 0,
                'submitted' => $statsRaw['submitted'] ?? 0,
                'acc' => $statsRaw['acc'] ?? 0,
                'revisi' => $statsRaw['revisi'] ?? 0,
            ];
            
            // Stats for Bar Chart (Kinerja PIC)
            $picStatsQuery = TaskAssignment::where('status', 'acc')->selectRaw('user_id, count(*) as total');
            $picStatsQuery = $applyFilters($picStatsQuery);
            $picStatsRaw = $picStatsQuery->groupBy('user_id')->orderBy('total', 'desc')->with('user')->take(10)->get();
            
            $picStats = [
                'labels' => [],
                'data' => []
            ];
            foreach ($picStatsRaw as $row) {
                if ($row->user) {
                    $picStats['labels'][] = explode(' ', trim($row->user->name))[0]; // first name only for brevity
                    $picStats['data'][] = $row->total;
                }
            }
            
            $users = \App\Models\User::where('role', 'user')->orderBy('name')->get();
        }

        return view('dashboard', compact('pendingTasks', 'waitingReviews', 'stats', 'picStats', 'submittedTasks', 'accTasks', 'revisiTasks', 'year', 'pic_id', 'users'));
    }
}
