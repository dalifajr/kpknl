<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncSsoUsersCommand extends Command
{
    protected $signature = 'monlap:sync-sso-users';

    protected $description = 'Sinkronisasi user penugasan (PIC) MonLap dari portal SSO KPKNL';

    public function handle(): int
    {
        $this->info('Memulai sinkronisasi user MonLap dari SSO...');

        User::syncFromSso();

        $assignable = User::where('role', 'user')->orderBy('name', 'asc')->get();
        $this->info("Sinkronisasi selesai. Ditemukan {$assignable->count()} user PIC yang berhak menerima penugasan:");

        foreach ($assignable as $u) {
            $this->line(" - [ID {$u->id} | SSO: {$u->sso_id}] {$u->name} ({$u->email})");
        }

        return Command::SUCCESS;
    }
}
