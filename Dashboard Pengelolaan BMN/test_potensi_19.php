<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BmnPotensiIdle;

$kemenkeu = BmnPotensiIdle::where('is_kemenkeu', true)->get();
echo "Total Kemenkeu rows: " . $kemenkeu->count() . "\n";

// Check KPP Pratama Baturaja & Kanwil DJPb
$potensiIdle19 = BmnPotensiIdle::where('is_kemenkeu', true)
    ->where(function($q) {
        $q->where('nama_satker', 'LIKE', '%DJPB%')
          ->orWhere('nama_satker', 'LIKE', '%PERBENDAHARAAN%')
          ->orWhere('nama_satker', 'LIKE', '%BATURAJA%');
    })
    ->get();

echo "Potensi Idle 19 target count: " . $potensiIdle19->count() . "\n";
foreach ($potensiIdle19 as $i => $item) {
    echo ($i+1) . ". Satker: {$item->nama_satker} | KdBrg: {$item->kode_barang} | NUP: {$item->nup} | Nama: {$item->nama_barang} | SBSK: {$item->hasil_pengukuran_sbsk}\n";
}
