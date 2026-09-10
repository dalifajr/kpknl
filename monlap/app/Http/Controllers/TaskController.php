<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Subbidang;
use App\Services\TaskSyncService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $search = $request->query('search');

        $query = Task::with('users')->orderBy('created_at', 'desc');

        if ($userId) {
            $query->whereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            });
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $perPage = $request->query('per_page', 10);
        $tasks = $query->paginate($perPage)->withQueryString();
        $users = \App\Models\User::where('role', 'user')->orderBy('name', 'asc')->get();

        return view('tasks.index', compact('tasks', 'users', 'userId', 'search'));
    }

    public function create()
    {
        // Sync & Get all users from SSO
        \App\Models\User::syncFromSso();
        $users = \App\Models\User::where('role', 'user')->orderBy('name', 'asc')->get();
        
        return view('tasks.create', compact('users'));
    }

    public function store(Request $request, TaskSyncService $syncService)
    {
        $request->merge([
            'deadline_next_month' => $request->has('deadline_next_month'),
            'is_recurring' => $request->has('is_recurring'),
        ]);

        $rules = [
            'title' => 'required|string|max:255',
            'period_type' => 'required|in:bulanan,triwulan,semesteran,tahunan,tidak rutin,custom',
            'deadline_type' => 'required|in:hari_kerja,fixed',
            'deadline_next_month' => 'boolean',
            'is_recurring' => 'boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ];

        if ($request->period_type === 'custom') {
            $rules['custom_start_date'] = 'required|date';
            $rules['custom_end_date'] = 'required|date|after_or_equal:custom_start_date';
            if ($request->is_recurring) {
                $rules['recurring_interval'] = 'required|in:daily,weekly,monthly,triwulan,semesteran,yearly';
            }
        }

        $request->validate($rules);

        $task = Task::create($request->except('user_ids'));
        $task->users()->sync($request->user_ids);

        if ($task->period_type !== 'tidak rutin') {
            $syncService->syncForTask($task);
        } else {
            foreach ($request->user_ids as $userId) {
                \App\Models\TaskAssignment::firstOrCreate(
                    [
                        'task_id' => $task->id,
                        'user_id' => $userId,
                        'period' => 'Tugas Khusus',
                    ],
                    [
                        'deadline_date' => $task->deadline_rule,
                        'status' => 'pending',
                    ]
                );
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dibuat.');
    }

    public function show(Task $task)
    {
        $task->load('assignments.user', 'assignments.submission', 'users');
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        \App\Models\User::syncFromSso();
        $users = \App\Models\User::where('role', 'user')->orderBy('name', 'asc')->get();
        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task, TaskSyncService $syncService)
    {
        $request->merge([
            'deadline_next_month' => $request->has('deadline_next_month'),
            'is_recurring' => $request->has('is_recurring'),
        ]);

        $rules = [
            'title' => 'required|string|max:255',
            'period_type' => 'required|in:bulanan,triwulan,semesteran,tahunan,tidak rutin,custom',
            'deadline_type' => 'required|in:hari_kerja,fixed',
            'deadline_next_month' => 'boolean',
            'is_recurring' => 'boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ];

        if ($request->period_type === 'custom') {
            $rules['custom_start_date'] = 'required|date';
            $rules['custom_end_date'] = 'required|date|after_or_equal:custom_start_date';
            if ($request->is_recurring) {
                $rules['recurring_interval'] = 'required|in:daily,weekly,monthly,triwulan,semesteran,yearly';
            }
        }

        $request->validate($rules);

        $task->update($request->except('user_ids'));
        $task->users()->sync($request->user_ids);

        if ($task->period_type !== 'tidak rutin') {
            $syncService->syncForTask($task);
        } else {
            foreach ($request->user_ids as $userId) {
                \App\Models\TaskAssignment::firstOrCreate(
                    [
                        'task_id' => $task->id,
                        'user_id' => $userId,
                        'period' => 'Tugas Khusus',
                    ],
                    [
                        'deadline_date' => $task->deadline_rule,
                        'status' => 'pending',
                    ]
                );
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil diupdate.');
    }

    public function toggleActive(Task $task, TaskSyncService $syncService)
    {
        $task->update(['is_active' => !$task->is_active]);
        
        // Re-sync assignments when task is activated
        if ($task->is_active) {
            $syncService->syncForTask($task);
        }
        
        return redirect()->route('tasks.index')->with('success', 'Status tugas berhasil diubah.');
    }

    public function destroy(Task $task)
    {
        if ($task->assignments()->whereNotIn('status', ['pending', 'draft'])->exists()) {
            return redirect()->route('tasks.index')->withErrors(['error' => 'Tugas "' . $task->title . '" tidak dapat dihapus karena sudah ada laporan yang masuk. Silakan batalkan pengisian melalui menu Review Laporan terlebih dahulu.']);
        }
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Tugas berhasil dihapus.');
    }

    public function bulkAssign(Request $request, TaskSyncService $syncService)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $tasks = Task::whereIn('id', $request->task_ids)->get();
        $targetUserIds = $request->input('user_ids', []);

        foreach ($tasks as $task) {
            $currentUserIds = $task->users()->pluck('users.id')->toArray();
            
            // Unassigned users (previously assigned, but now unchecked)
            $unassignedUserIds = array_diff($currentUserIds, $targetUserIds);
            
            // Newly assigned users (previously unassigned, now checked)
            $newlyAssignedUserIds = array_diff($targetUserIds, $currentUserIds);

            // Sync task-user relationship
            $task->users()->sync($targetUserIds);

            // Delete pending or draft assignments for unassigned users
            if (!empty($unassignedUserIds)) {
                \App\Models\TaskAssignment::where('task_id', $task->id)
                    ->whereIn('user_id', $unassignedUserIds)
                    ->whereIn('status', ['pending', 'draft'])
                    ->delete();
            }

            // Generate assignments for newly assigned users
            if (!empty($newlyAssignedUserIds)) {
                if ($task->period_type !== 'tidak rutin') {
                    $syncService->syncForTask($task);
                } else {
                    foreach ($newlyAssignedUserIds as $userId) {
                        \App\Models\TaskAssignment::firstOrCreate(
                            [
                                'task_id' => $task->id,
                                'user_id' => $userId,
                                'period' => 'Tugas Khusus',
                            ],
                            [
                                'deadline_date' => $task->deadline_rule,
                                'status' => 'pending',
                            ]
                        );
                    }
                }
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Pengaturan penugasan user PIC berhasil diperbarui.');
    }

    public function previewDeadline(Request $request)
    {
        $task = new \App\Models\Task();
        $task->period_type = $request->period_type;
        $task->deadline_type = $request->deadline_type;
        $task->deadline_rule = $request->deadline_rule;
        $task->deadline_next_month = filter_var($request->deadline_next_month, FILTER_VALIDATE_BOOLEAN);
        $task->is_recurring = filter_var($request->is_recurring, FILTER_VALIDATE_BOOLEAN);
        $task->recurring_interval = $request->recurring_interval;
        $task->custom_start_date = $request->custom_start_date;
        $task->custom_end_date = $request->custom_end_date;

        if ($task->period_type === 'tidak rutin' || !$task->period_type) {
            return response()->json(null);
        }

        $now = \Carbon\Carbon::now();
        $label = '';
        $deadlineDate = null;
        $baseDate = null;
        $endOfPeriod = null;

        $rule = (int) $task->deadline_rule;
        
        switch ($task->period_type) {
            case 'bulanan':
                $label = $now->translatedFormat('F Y');
                $baseDate = \Carbon\Carbon::create($now->year, $now->month, 1);
                $endOfPeriod = $now->copy()->endOfMonth();
                break;
            case 'triwulan':
                $quarter = ceil($now->month / 3);
                $label = "Triwulan {$quarter} {$now->year}";
                $lastMonthOfQuarter = $quarter * 3;
                $baseDate = \Carbon\Carbon::create($now->year, $lastMonthOfQuarter - 2, 1);
                $endOfPeriod = \Carbon\Carbon::create($now->year, $lastMonthOfQuarter, 1)->endOfMonth();
                break;
            case 'semesteran':
                $semester = $now->month <= 6 ? 1 : 2;
                $label = "Semester {$semester} {$now->year}";
                $lastMonthOfSemester = $semester == 1 ? 6 : 12;
                $baseDate = \Carbon\Carbon::create($now->year, $semester == 1 ? 1 : 7, 1);
                $endOfPeriod = \Carbon\Carbon::create($now->year, $lastMonthOfSemester, 1)->endOfMonth();
                break;
            case 'tahunan':
                $label = "Tahun {$now->year}";
                $baseDate = \Carbon\Carbon::create($now->year, 1, 1);
                $endOfPeriod = $now->copy()->endOfYear();
                break;
            case 'custom':
                if (!$task->custom_start_date || !$task->custom_end_date) {
                    return response()->json(null);
                }
                $start = \Carbon\Carbon::parse($task->custom_start_date);
                $end = \Carbon\Carbon::parse($task->custom_end_date);
                $label = "Custom (" . $start->format('d/m') . " - " . $end->format('d/m') . ")";
                $deadlineDate = $end->copy()->endOfDay();
                break;
        }

        $skippedDays = [];

        if ($task->period_type !== 'custom') {
            if ($task->deadline_next_month) {
                $baseDate = $endOfPeriod->copy()->addDay()->startOfDay();
            }

            if ($rule > 0) {
                if ($task->deadline_type === 'hari_kerja') {
                    $currentDate = $baseDate->copy()->startOfDay();
                    $addedDays = 0;
                    $holidays = \App\Models\Holiday::all()->keyBy('date')->toArray();
                    
                    // Include Holiday Agendas
                    $holidayAgendas = \App\Models\Agenda::where('is_holiday', true)->get();
                    foreach ($holidayAgendas as $agenda) {
                        $start = \Carbon\Carbon::parse($agenda->start_date);
                        $end = $agenda->end_date ? \Carbon\Carbon::parse($agenda->end_date) : $start->copy();
                        while ($start->lte($end)) {
                            $dStr = $start->format('Y-m-d');
                            if (!isset($holidays[$dStr])) {
                                $holidays[$dStr] = ['name' => 'Agenda Libur: ' . $agenda->title];
                            }
                            $start->addDay();
                        }
                    }
                    
                    $checkAndSkip = function($date, &$skippedDays) use ($holidays) {
                        $isWeekend = in_array($date->dayOfWeek, [\Carbon\Carbon::FRIDAY, \Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
                        $dateStr = $date->format('Y-m-d');
                        $isHoliday = isset($holidays[$dateStr]);
                        
                        if ($isHoliday) {
                            $skippedDays[] = $date->translatedFormat('d M Y') . " ({$holidays[$dateStr]['name']})";
                            return true;
                        } elseif ($isWeekend) {
                            $dayName = $date->translatedFormat('l');
                            $skippedDays[] = $date->translatedFormat('d M Y') . " ({$dayName})";
                            return true;
                        }
                        return false;
                    };
                    
                    while ($checkAndSkip($currentDate, $skippedDays)) {
                        $currentDate->addDay();
                    }

                    while ($addedDays < $rule - 1) {
                        $currentDate->addDay();
                        if (!$checkAndSkip($currentDate, $skippedDays)) {
                            $addedDays++;
                        }
                    }
                    $deadlineDate = $currentDate->endOfDay();
                } else {
                    $deadlineDate = $baseDate->copy()->addDays($rule - 1)->endOfDay();
                }
            } else {
                if ($task->deadline_next_month) {
                    $deadlineDate = $baseDate->copy()->endOfMonth();
                } else {
                    $deadlineDate = $endOfPeriod->copy()->endOfDay();
                }
            }
        }

        $nextPeriodStr = null;
        if ($task->is_recurring && $task->recurring_interval && $task->period_type === 'custom') {
            if ($task->recurring_interval === 'daily') $nextPeriodStr = $start->copy()->addDay()->translatedFormat('d F Y');
            if ($task->recurring_interval === 'weekly') $nextPeriodStr = $start->copy()->addWeek()->translatedFormat('d F Y');
            if ($task->recurring_interval === 'monthly') $nextPeriodStr = $start->copy()->addMonth()->translatedFormat('F Y');
            if ($task->recurring_interval === 'triwulan') $nextPeriodStr = $start->copy()->addMonths(3)->translatedFormat('F Y');
            if ($task->recurring_interval === 'semesteran') $nextPeriodStr = $start->copy()->addMonths(6)->translatedFormat('F Y');
            if ($task->recurring_interval === 'yearly') $nextPeriodStr = $start->copy()->addYear()->translatedFormat('Y');
        } elseif ($task->period_type !== 'custom') {
            if ($task->period_type === 'bulanan') $nextPeriodStr = $now->copy()->addMonth()->translatedFormat('F Y');
            if ($task->period_type === 'triwulan') $nextPeriodStr = "Triwulan Selanjutnya";
            if ($task->period_type === 'semesteran') $nextPeriodStr = "Semester Selanjutnya";
            if ($task->period_type === 'tahunan') $nextPeriodStr = $now->copy()->addYear()->translatedFormat('Y');
        }

        return response()->json([
            'label' => $label,
            'deadline_date' => $deadlineDate ? $deadlineDate->translatedFormat('l, d F Y') : null,
            'skipped_days' => $skippedDays,
            'next_period' => $nextPeriodStr
        ]);
    }

    public function bulkAction(Request $request, TaskSyncService $syncService)
    {
        $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'bulk_action' => 'required|in:nonaktifkan,aktifkan,hapus',
        ]);

        if ($request->bulk_action === 'nonaktifkan') {
            Task::whereIn('id', $request->task_ids)->update(['is_active' => false]);
            $msg = 'Tugas terpilih berhasil dinonaktifkan.';
        } elseif ($request->bulk_action === 'aktifkan') {
            Task::whereIn('id', $request->task_ids)->update(['is_active' => true]);
            // Re-sync assignments for newly activated tasks
            $tasks = Task::whereIn('id', $request->task_ids)->get();
            foreach ($tasks as $task) {
                $syncService->syncForTask($task);
            }
            $msg = 'Tugas terpilih berhasil diaktifkan dan penugasan telah di-generate.';
        } elseif ($request->bulk_action === 'hapus') {
            $tasks = Task::whereIn('id', $request->task_ids)->get();
            $deletedCount = 0;
            $failedTasks = [];
            
            foreach ($tasks as $task) {
                if ($task->assignments()->whereNotIn('status', ['pending', 'draft'])->exists()) {
                    $failedTasks[] = $task->title;
                } else {
                    $task->delete();
                    $deletedCount++;
                }
            }
            
            if (count($failedTasks) > 0) {
                return redirect()->route('tasks.index')->withErrors([
                    'error' => "Berhasil menghapus $deletedCount tugas. Beberapa tugas gagal dihapus karena sudah memiliki isian laporan: " . implode(', ', $failedTasks) . "."
                ]);
            }
            
            $msg = 'Tugas terpilih berhasil dihapus.';
        }

        return redirect()->route('tasks.index')->with('success', $msg);
    }
}
