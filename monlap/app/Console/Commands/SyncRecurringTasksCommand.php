<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncRecurringTasksCommand extends Command
{
    protected $signature = 'monlap:sync-recurring';

    protected $description = 'Generate new assignments for recurring custom tasks that have passed their deadline.';

    public function handle(\App\Services\TaskSyncService $syncService)
    {
        $tasks = \App\Models\Task::where('is_active', true)->get();

        foreach ($tasks as $task) {
            if ($task->period_type === 'custom') {
                if (!$task->is_recurring || !$task->custom_end_date) continue;
                
                $endDate = \Carbon\Carbon::parse($task->custom_end_date)->endOfDay();
                
                // If the deadline has passed, create the next cycle
                if (\Carbon\Carbon::now()->isAfter($endDate)) {
                    $startDate = \Carbon\Carbon::parse($task->custom_start_date);
                    
                    switch ($task->recurring_interval) {
                        case 'daily':
                            $startDate->addDay();
                            $endDate->addDay();
                            break;
                        case 'weekly':
                            $startDate->addWeek();
                            $endDate->addWeek();
                            break;
                        case 'monthly':
                            $startDate->addMonthNoOverflow();
                            $endDate->addMonthNoOverflow();
                            break;
                        case 'triwulan':
                            $startDate->addMonthsNoOverflow(3);
                            $endDate->addMonthsNoOverflow(3);
                            break;
                        case 'semesteran':
                            $startDate->addMonthsNoOverflow(6);
                            $endDate->addMonthsNoOverflow(6);
                            break;
                        case 'yearly':
                            $startDate->addYearNoOverflow();
                            $endDate->addYearNoOverflow();
                            break;
                    }

                    // Update task with new dates
                    $task->update([
                        'custom_start_date' => $startDate->format('Y-m-d'),
                        'custom_end_date' => $endDate->format('Y-m-d'),
                    ]);

                    // Sync new assignments
                    $syncService->syncForTask($task);
                    
                    $this->info("Generated new cycle for custom task: {$task->title}");
                }
            } elseif ($task->period_type !== 'tidak rutin') {
                // For bulanan, triwulan, dll, we just call syncForTask. 
                // firstOrCreate inside syncForTask prevents duplication.
                $syncService->syncForTask($task);
                $this->info("Synced standard period for task: {$task->title}");
            }
        }
        
        $this->info("Recurring tasks synced.");
    }
}
