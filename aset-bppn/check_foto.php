<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$a = \App\Models\Asset::whereNotNull('foto')->first();
if ($a) {
    dump($a->foto);
    $arr = json_decode($a->foto, true);
    if (is_array($arr)) {
        foreach($arr as $p) {
            echo storage_path('app/public/' . $p) . "\n";
            echo file_exists(storage_path('app/public/' . $p)) ? "EXISTS\n" : "NOT FOUND\n";
        }
    } else {
        echo "NOT JSON\n";
        echo storage_path('app/public/' . $a->foto) . "\n";
        echo file_exists(storage_path('app/public/' . $a->foto)) ? "EXISTS\n" : "NOT FOUND\n";
    }
} else {
    echo "NO ASSETS WITH PHOTOS\n";
}
