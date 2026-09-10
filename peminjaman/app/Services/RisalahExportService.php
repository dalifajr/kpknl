<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class RisalahExportService
{
    /**
     * Export data rows as standard CSV download.
     */
    public function exportCsv(string $filename, array $headers, $query, callable $rowMapper): StreamedResponse
    {
        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($headers, $query, $rowMapper) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write CSV headers
            fputcsv($handle, $headers);

            // Chunk query to avoid memory exhaustion
            $query->chunk(500, function ($rows) use ($handle, $rowMapper) {
                foreach ($rows as $row) {
                    fputcsv($handle, $rowMapper($row));
                }
            });

            fclose($handle);
        }, 200, $responseHeaders);
    }
}
