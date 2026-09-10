<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$assets = \App\Models\Asset::where('id', 2)->with(['province', 'regency', 'district', 'village'])->get();
$pdf = \Barryvdh\DomPDF\Facade\Pdf::setOptions(['isRemoteEnabled' => true])->loadView('assets.buku_profil_pdf', compact('assets'))->setPaper('legal', 'portrait');
$pdf->save(public_path('test_output.pdf'));
echo 'OK';
