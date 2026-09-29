<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = new App\Services\GoogleSheetSyncService();
$csvUrl = $service->convertToCsvUrl(null);
echo "CSV URL: " . $csvUrl . "\n";
$content = @file_get_contents($csvUrl);
if ($content) {
    $lines = explode("\n", $content);
    foreach ($lines as $idx => $line) {
        if (str_contains(strtoupper($line), 'NIP')) {
            echo "Header line " . ($idx + 1) . ":\n";
            $cols = str_getcsv($line);
            foreach ($cols as $cIdx => $c) {
                if (trim($c)) {
                    echo "  Col " . ($cIdx + 1) . ": [" . trim($c) . "]\n";
                }
            }
            break;
        }
    }
} else {
    echo "Failed to fetch CSV\n";
}
