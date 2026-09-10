<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetSyncService;
use Illuminate\Console\Command;

class SyncBmnDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bmn:sync {--source=AUTO_SCHEDULER : Sumber pemicu sinkronisasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi data BMN Potensi Idle dan Eks BMN Idle dari Google Spreadsheet ke database lokal';

    /**
     * Execute the console command.
     */
    public function handle(GoogleSheetSyncService $syncService): int
    {
        $this->info('Memulai sinkronisasi data BMN dari Google Spreadsheet...');
        
        $source = $this->option('source') ?: 'ARTISAN_CLI';

        try {
            $result = $syncService->sync($source);
            
            $this->info("✓ " . $result['message']);
            $this->table(
                ['Metrik', 'Nilai'],
                [
                    ['Tipe Sumber', $result['source_type']],
                    ['Potensi Idle Tersinkron', $result['potensi_synced']],
                    ['Eks BMN Idle Tersinkron', $result['eks_idle_synced']],
                    ['Durasi Sinkronisasi', $result['duration_seconds'] . ' detik'],
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Gagal melakukan sinkronisasi: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
