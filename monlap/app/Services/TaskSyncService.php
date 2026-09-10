<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Holiday;
use App\Models\Agenda;
use Carbon\Carbon;

class TaskSyncService
{
    /**
     * Generate assignments for a specific task and period.
     */
    public function syncForTask(Task $task)
    {
        if (!$task->is_active) {
            return;
        }
        // Get users explicitly assigned to this task via pivot table
        $users = $task->users()->where('role', 'user')->get();
        
        if ($users->isEmpty()) {
            return;
        }

        $periodData = $this->getCurrentPeriodData($task);
        
        if (!$periodData) {
            // Either "tidak rutin" or invalid period type
            return;
        }

        $periodLabel = $periodData['label'];
        $deadlineDate = $periodData['deadline_date'];

        foreach ($users as $user) {
            TaskAssignment::firstOrCreate(
                [
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'period' => $periodLabel,
                ],
                [
                    'open_date' => $periodData['open_date'] ?? null,
                    'deadline_date' => $deadlineDate,
                    'status' => 'pending',
                ]
            );
        }
    }

    /**
     * Determine current period label and deadline based on task type.
     */
    public function getCurrentPeriodData(Task $task)
    {
        $now = Carbon::now();
        $label = '';
        $deadlineDate = null;
        $openDate = null;

        $rule = (int) $task->deadline_rule; 
        
        switch ($task->period_type) {
            case 'bulanan':
                $label = $now->translatedFormat('F Y');
                $baseDate = Carbon::create($now->year, $now->month, 1);
                $endOfPeriod = $now->copy()->endOfMonth();
                break;
                
            case 'triwulan':
                $quarter = ceil($now->month / 3);
                $label = "Triwulan {$quarter} {$now->year}";
                $lastMonthOfQuarter = $quarter * 3;
                $baseDate = Carbon::create($now->year, $lastMonthOfQuarter - 2, 1);
                $endOfPeriod = Carbon::create($now->year, $lastMonthOfQuarter, 1)->endOfMonth();
                break;

            case 'semesteran':
                $semester = $now->month <= 6 ? 1 : 2;
                $label = "Semester {$semester} {$now->year}";
                $lastMonthOfSemester = $semester == 1 ? 6 : 12;
                $baseDate = Carbon::create($now->year, $semester == 1 ? 1 : 7, 1);
                $endOfPeriod = Carbon::create($now->year, $lastMonthOfSemester, 1)->endOfMonth();
                break;

            case 'tahunan':
                $label = "Tahun {$now->year}";
                $baseDate = Carbon::create($now->year, 1, 1);
                $endOfPeriod = $now->copy()->endOfYear();
                break;
                
            case 'tidak rutin':
                return null;
                
            case 'custom':
                if (!$task->custom_start_date || !$task->custom_end_date) {
                    return null;
                }
                $start = Carbon::parse($task->custom_start_date);
                $end = Carbon::parse($task->custom_end_date);
                $label = "Custom (" . $start->format('d/m') . " - " . $end->format('d/m') . ")";
                $openDate = $start;
                $deadlineDate = $end->endOfDay();
                break;
        }

        if ($task->period_type !== 'custom') {
            if ($task->deadline_next_month) {
                // If deadline is in the next month after the cycle ends
                $baseDate = $endOfPeriod->copy()->addDay()->startOfDay();
            }

            if ($rule > 0) {
                if ($task->deadline_type === 'hari_kerja') {
                    $deadlineDate = $this->calculateWorkingDays($baseDate, $rule);
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

        return [
            'label' => $label,
            'open_date' => $openDate ? $openDate->format('Y-m-d') : null,
            'deadline_date' => $deadlineDate ? $deadlineDate->format('Y-m-d') : null,
        ];
    }

    /**
     * Get all excluded holiday dates (National Holidays + Agenda Holidays).
     */
    public function getAllHolidays(): array
    {
        $holidays = Holiday::pluck('date')->toArray();

        $holidayAgendas = Agenda::where('is_holiday', true)->get();
        foreach ($holidayAgendas as $agenda) {
            $start = Carbon::parse($agenda->start_date);
            $end = $agenda->end_date ? Carbon::parse($agenda->end_date) : $start->copy();

            while ($start->lte($end)) {
                $holidays[] = $start->format('Y-m-d');
                $start->addDay();
            }
        }

        return array_values(array_unique($holidays));
    }

    /**
     * Calculate date by adding N working days.
     * Excludes Friday, Saturday, Sunday and National/Agenda Holidays.
     */
    public function calculateWorkingDays(Carbon $baseDate, int $daysToAdd)
    {
        $currentDate = $baseDate->copy()->startOfDay();
        $addedDays = 0;
        
        // Fetch all national & agenda holidays
        $holidays = $this->getAllHolidays();

        // First, check if baseDate is a working day, if not advance to the next working day
        while ($this->isNonWorkingDay($currentDate, $holidays)) {
            $currentDate->addDay();
        }

        while ($addedDays < $daysToAdd - 1) {
            $currentDate->addDay();
            if (!$this->isNonWorkingDay($currentDate, $holidays)) {
                $addedDays++;
            }
        }

        return $currentDate->endOfDay();
    }

    public function isNonWorkingDay(Carbon $date, array $holidays)
    {
        // 5 = Friday (WFH), 6 = Saturday, 0 = Sunday (Weekend)
        if (in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY, Carbon::SUNDAY])) {
            return true;
        }

        if (in_array($date->format('Y-m-d'), $holidays)) {
            return true;
        }

        return false;
    }

    /**
     * Recalculate deadline_date for all active assignments when holidays/agendas change.
     */
    public function recalculateAllDeadlines()
    {
        $tasks = Task::where('is_active', true)->where('period_type', '!=', 'tidak rutin')->get();
        foreach ($tasks as $task) {
            $periodData = $this->getCurrentPeriodData($task);
            if ($periodData && !empty($periodData['deadline_date'])) {
                TaskAssignment::where('task_id', $task->id)
                    ->where('period', $periodData['label'])
                    ->where('status', '!=', 'approved')
                    ->update([
                        'deadline_date' => $periodData['deadline_date'],
                    ]);
            }
        }
    }
}
