<?php

namespace App\Console\Commands;

use App\Models\TaskAssignment;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monlap:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat deadline tugas ke User PIC via SSO Notification Hub';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('Starting to scan for reminders...');
        
        $assignments = TaskAssignment::with('user', 'task')
            ->whereIn('status', ['pending', 'draft', 'revisi'])
            ->whereNotNull('deadline_date')
            ->get();

        $now = Carbon::now()->startOfDay();

        $count = 0;
        foreach ($assignments as $assignment) {
            $deadline = Carbon::parse($assignment->deadline_date)->startOfDay();
            $diffDays = $now->diffInDays($deadline, false); // false = return negative if past
            
            if (in_array($diffDays, [7, 3, 0])) {
                if ($diffDays == 0) {
                    $title = 'HARI INI DEADLINE: ' . $assignment->task->title;
                    $message = 'Hari ini adalah batas waktu akhir untuk laporan ' . $assignment->period;
                } else {
                    $title = 'PENGINGAT ' . $diffDays . ' HARI: ' . $assignment->task->title;
                    $message = 'Laporan untuk periode ' . $assignment->period . ' akan jatuh tempo dalam ' . $diffDays . ' hari.';
                }
                
                $link = config('app.url') . '/user-tasks/' . $assignment->id;
                
                $notificationService->send($assignment->user, $title, $message, $link, 'task');
                $count++;
            }
        }
        
        $this->info("Sent {$count} reminders successfully.");
    }
}
