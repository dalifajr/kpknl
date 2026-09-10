<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FetchHolidaysCommand extends Command
{
    protected $signature = 'monlap:fetch-holidays {year?}';

    protected $description = 'Fetch national holidays from API and save to database';

    public function handle()
    {
        $year = $this->argument('year') ?: date('Y');
        $this->info("Fetching holidays for year {$year}...");

        $url = "https://api-harilibur.vercel.app/api?month=0&year={$year}";
        $response = \Illuminate\Support\Facades\Http::get($url);

        if ($response->successful()) {
            $holidays = $response->json();
            $count = 0;
            
            foreach ($holidays as $holiday) {
                if (isset($holiday['is_national_holiday']) && $holiday['is_national_holiday']) {
                    \App\Models\Holiday::updateOrCreate(
                        ['date' => $holiday['tanggal']],
                        [
                            'name' => $holiday['keterangan'],
                            'is_national_holiday' => true,
                        ]
                    );
                    $count++;
                }
            }
            $this->info("Successfully fetched and saved {$count} national holidays for {$year}.");
        } else {
            $this->error("Failed to fetch holidays from API.");
        }
    }
}
