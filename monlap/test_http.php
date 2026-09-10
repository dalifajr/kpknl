<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    $res = Illuminate\Support\Facades\Http::get('http://sso-kpknl-palembang.test/api/user');
    echo "OK: " . $res->status();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
