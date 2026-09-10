<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$markerUrl = 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png';
$markerOpts = ["http" => ["method" => "GET", "header" => "User-Agent: AsetBPPN/1.0\r\n"]];
$markerContext = stream_context_create($markerOpts);
$markerData = @file_get_contents($markerUrl, false, $markerContext);
$markerBase64 = 'data:image/png;base64,' . base64_encode($markerData);

$html = "
<html><head><style>
.map-container { position: relative; width: 300px; height: 180px; background: #ddd; margin: 0 auto; }
.popup-box { position: absolute; top: 20px; left: 50%; margin-left: -75px; width: 150px; background: white; border: 1px solid #ccc; border-radius: 8px; padding: 8px; font-size: 11px; text-align: left; font-family: sans-serif; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
.popup-title { font-weight: bold; font-size: 12px; color: #333; margin-bottom: 2px; }
.popup-text { color: #555; }
.marker-icon { position: absolute; top: 90px; left: 50%; margin-left: -12px; width: 25px; }
</style></head>
<body>
<div class='map-container'>
    <div class='popup-box'>
        <div class='popup-title'>PRK-01291012</div>
        <div class='popup-text'>Jalan Pangeran Ratu</div>
    </div>
    <div class='marker-icon'>
        <img src='{$markerBase64}' style='width: 25px;'>
    </div>
</div>
</body></html>";

$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
$pdf->save(public_path("test_overlay.pdf"));
echo "OK";
