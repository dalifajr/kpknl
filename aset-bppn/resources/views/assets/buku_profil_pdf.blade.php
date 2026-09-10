<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Buku Profil Aset</title>
    <style>
        @page {
            size: legal portrait;
            margin: 20mm 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .header-number {
            font-size: 14pt;
            font-weight: bold;
            color: #003a6c; /* Dark blue/teal approximation */
            margin-bottom: 10px;
        }
        .header-title {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
            line-height: 1.2;
        }
        .header-address {
            font-size: 11px;
            margin-top: 5px;
            text-transform: uppercase;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
        }
        .main-table td {
            border: 1px solid #000;
        }

        .no-border-table td {
            border: none;
        }

        .info-table, .batas-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .info-table td, .batas-table td {
            padding: 3px 0;
            vertical-align: top;
            border: none;
        }
        .info-label {
            width: 130px;
        }
        .info-colon {
            width: 15px;
            text-align: left;
        }

        .batas-table { margin-top: 30px; }
        .batas-header td { font-weight: bold; padding-bottom: 10px; }
        
        .col-batas-label { width: 60px; }
        .col-batas-colon { width: 15px; }
        .col-batas-value { width: 200px; }

        .col-ket-label { width: 100px; }
        .col-ket-colon { width: 15px; }
        .col-ket-value { width: auto; }

        .page-break { page-break-after: always; }
        
        .media-img {
            max-width: 100%;
            max-height: 250px;
        }

        .map-container {
            position: relative;
            width: 100%;
            height: 250px;
            background: #eee;
            margin: 0 auto;
            overflow: hidden;
        }
        .map-container.small {
            height: 180px;
        }
        .popup-box {
            position: absolute;
            top: 20px;
            left: 50%;
            margin-left: -75px;
            width: 150px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px;
            font-size: 11px;
            text-align: left;
            font-family: sans-serif;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 10;
        }
        .popup-title { font-weight: bold; font-size: 12px; color: #333; margin-bottom: 2px; }
        .popup-text { color: #555; }
        .marker-icon {
            position: absolute;
            top: 100px;
            left: 50%;
            margin-left: -12px;
            width: 25px;
            z-index: 5;
        }
        .marker-icon.small {
            top: 70px;
        }
    </style>
</head>
<body>
    @foreach($assets as $index => $asset)
    
    @php
        $zoom = 16;
        $lat = $asset->koordinat_latitude;
        $lon = $asset->koordinat_longitude;
        $mapBase64 = null;
        if ($lat && $lon) {
            $x = floor((($lon + 180) / 360) * pow(2, $zoom));
            $y = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) / 2 * pow(2, $zoom));
            $mapUrl = "https://a.tile.openstreetmap.org/{$zoom}/{$x}/{$y}.png";
            $opts = [
                "http" => [
                    "method" => "GET",
                    "header" => "User-Agent: AsetBPPN/1.0\r\n"
                ]
            ];
            $context = stream_context_create($opts);
            $mapData = @file_get_contents($mapUrl, false, $context);
            if ($mapData) {
                $mapBase64 = 'data:image/png;base64,' . base64_encode($mapData);
            }
        }
        
        $markerUrl = 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png';
        $markerOpts = ["http" => ["method" => "GET", "header" => "User-Agent: AsetBPPN/1.0\r\n"]];
        $markerContext = stream_context_create($markerOpts);
        $markerData = @file_get_contents($markerUrl, false, $markerContext);
        $markerBase64 = $markerData ? 'data:image/png;base64,' . base64_encode($markerData) : '';
        
        $photos = [];
        if($asset->foto_paths) {
            $fotoPaths = is_array($asset->foto_paths) ? $asset->foto_paths : json_decode($asset->foto_paths, true);
            if(is_array($fotoPaths)) {
                foreach($fotoPaths as $path) {
                    $fullPath = storage_path('app/public/' . $path);
                    if (file_exists($fullPath)) {
                        $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                        $data = file_get_contents($fullPath);
                        $photos[] = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                }
            }
        }
    @endphp

    <div class="header-number">{{ $index + 1 }}. {{ $asset->kode_aset }} {{ strtoupper($asset->regency->name ?? '') }}</div>
    
    <table class="main-table">
        <tr>
            <td colspan="2" class="text-center" style="padding: 15px;">
                <h2 class="header-title">PROFIL ASET PROPERTI</h2>
                <h2 class="header-title">EKS BADAN PENYEHATAN PERBANKAN NASIONAL</h2>
                <div class="header-address">
                    {{ $asset->alamat_namajalan ?? '-' }}, RT/RW: {{ $asset->alamat_rt_rw ?? '-' }}, KEL. {{ $asset->village->name ?? '-' }}, KEC. {{ $asset->district->name ?? '-' }}
                </div>
            </td>
        </tr>
        
        <!-- Foto & Mini Map Area (Adaptive High-Clarity Multi-Photo Layout) -->
        @php
            $totalPhotos = count($photos);
        @endphp

        @if($totalPhotos === 0)
            <tr>
                <td colspan="2" style="padding: 10px; text-align: center;">
                    @if($mapBase64)
                        <div class="map-container" style="width: 60%; height: 260px; margin: 0 auto; position: relative; background: #eee; overflow: hidden; border-radius: 4px;">
                            <img src="{{ $mapBase64 }}" alt="Map" style="width: 100%; height: 260px; object-fit: cover;">
                            <div class="popup-box">
                                <div class="popup-title">{{ $asset->kode_aset }}</div>
                                <div class="popup-text">{{ $asset->alamat_namajalan }}</div>
                            </div>
                            <div class="marker-icon">
                                <img src="{{ $markerBase64 }}" style="width: 25px;">
                            </div>
                        </div>
                        <div style="margin-top: 6px;">
                            <a href="https://maps.google.com/?q={{ $lat }},{{ $lon }}" style="color: #003a6c; font-size: 10px; font-weight: bold; text-decoration: underline;">
                                Link Google Maps ({{ $lat }}, {{ $lon }})
                            </a>
                        </div>
                    @else
                        <div style="height: 240px; line-height: 240px; background: #fafafa; border: 1px dashed #bdbdbd; color: #757575; font-size: 11px;">
                            Foto & Peta Tidak Tersedia
                        </div>
                    @endif
                </td>
            </tr>
        @elseif($totalPhotos === 1)
            <tr>
                <!-- Kolom Kiri: Foto 1 (Besar, lebar 50%, tinggi 280px) -->
                <td style="width: 50%; padding: 8px; vertical-align: top; text-align: center;">
                    <div style="height: 280px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                        <img src="{{ $photos[0] }}" alt="Foto 1" style="width: 100%; height: 280px; object-fit: cover;">
                    </div>
                </td>
                <!-- Kolom Kanan: Peta (lebar 50%, tinggi 280px) -->
                <td style="width: 50%; padding: 8px; vertical-align: top; text-align: center;">
                    @if($mapBase64)
                        <div class="map-container" style="width: 100%; height: 260px; position: relative; background: #eee; overflow: hidden; border-radius: 4px;">
                            <img src="{{ $mapBase64 }}" alt="Map" style="width: 100%; height: 260px; object-fit: cover;">
                            <div class="popup-box">
                                <div class="popup-title">{{ $asset->kode_aset }}</div>
                                <div class="popup-text">{{ $asset->alamat_namajalan }}</div>
                            </div>
                            <div class="marker-icon">
                                <img src="{{ $markerBase64 }}" style="width: 25px;">
                            </div>
                        </div>
                        <div style="margin-top: 6px;">
                            <a href="https://maps.google.com/?q={{ $lat }},{{ $lon }}" style="color: #003a6c; font-size: 10px; font-weight: bold; text-decoration: underline;">
                                Link Google Maps ({{ $lat }}, {{ $lon }})
                            </a>
                        </div>
                    @else
                        <div style="height: 280px; line-height: 280px; background: #fafafa; border: 1px dashed #bdbdbd; color: #757575; font-size: 11px;">
                            Peta Koordinat Kosong
                        </div>
                    @endif
                </td>
            </tr>
        @elseif($totalPhotos === 2)
            <tr>
                <!-- Kolom Kiri: Foto 1 & Foto 2 Berjejer Vertikal -->
                <td style="width: 50%; padding: 8px; vertical-align: top; text-align: center;">
                    <div style="height: 142px; overflow: hidden; background: #f0f0f0; border-radius: 4px; margin-bottom: 6px;">
                        <img src="{{ $photos[0] }}" alt="Foto 1" style="width: 100%; height: 142px; object-fit: cover;">
                    </div>
                    <div style="height: 142px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                        <img src="{{ $photos[1] }}" alt="Foto 2" style="width: 100%; height: 142px; object-fit: cover;">
                    </div>
                </td>
                <!-- Kolom Kanan: Peta (tinggi 290px) -->
                <td style="width: 50%; padding: 8px; vertical-align: top; text-align: center;">
                    @if($mapBase64)
                        <div class="map-container" style="width: 100%; height: 268px; position: relative; background: #eee; overflow: hidden; border-radius: 4px;">
                            <img src="{{ $mapBase64 }}" alt="Map" style="width: 100%; height: 268px; object-fit: cover;">
                            <div class="popup-box">
                                <div class="popup-title">{{ $asset->kode_aset }}</div>
                                <div class="popup-text">{{ $asset->alamat_namajalan }}</div>
                            </div>
                            <div class="marker-icon">
                                <img src="{{ $markerBase64 }}" style="width: 25px;">
                            </div>
                        </div>
                        <div style="margin-top: 6px;">
                            <a href="https://maps.google.com/?q={{ $lat }},{{ $lon }}" style="color: #003a6c; font-size: 10px; font-weight: bold; text-decoration: underline;">
                                Link Google Maps ({{ $lat }}, {{ $lon }})
                            </a>
                        </div>
                    @else
                        <div style="height: 290px; line-height: 290px; background: #fafafa; border: 1px dashed #bdbdbd; color: #757575; font-size: 11px;">
                            Peta Koordinat Kosong
                        </div>
                    @endif
                </td>
            </tr>
        @elseif($totalPhotos === 3)
            <tr>
                <td colspan="2" style="padding: 6px 8px;">
                    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0;">
                        <tr>
                            <!-- Foto 1 (Lebar 50%, Tinggi 165px) -->
                            <td style="width: 50%; padding: 0 4px 6px 0; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 165px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[0] }}" alt="Foto 1" style="width: 100%; height: 165px; object-fit: cover;">
                                </div>
                            </td>
                            <!-- Foto 2 (Lebar 50%, Tinggi 165px) -->
                            <td style="width: 50%; padding: 0 0 6px 4px; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 165px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[1] }}" alt="Foto 2" style="width: 100%; height: 165px; object-fit: cover;">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <!-- Foto 3 (Lebar 50%, Tinggi 165px) -->
                            <td style="width: 50%; padding: 0 4px 0 0; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 165px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[2] }}" alt="Foto 3" style="width: 100%; height: 165px; object-fit: cover;">
                                </div>
                            </td>
                            <!-- Peta Satelit (Lebar 50%, Tinggi 165px) -->
                            <td style="width: 50%; padding: 0 0 0 4px; border: none; text-align: center; vertical-align: top;">
                                @if($mapBase64)
                                    <div class="map-container" style="width: 100%; height: 145px; position: relative; background: #eee; overflow: hidden; border-radius: 4px;">
                                        <img src="{{ $mapBase64 }}" alt="Map" style="width: 100%; height: 145px; object-fit: cover;">
                                        <div class="popup-box" style="top: 6px; padding: 4px 6px; width: 120px;">
                                            <div class="popup-title" style="font-size: 9px;">{{ $asset->kode_aset }}</div>
                                            <div class="popup-text" style="font-size: 8px;">{{ Str::limit($asset->alamat_namajalan, 25) }}</div>
                                        </div>
                                        <div class="marker-icon" style="top: 55px;">
                                            <img src="{{ $markerBase64 }}" style="width: 20px;">
                                        </div>
                                    </div>
                                    <div style="margin-top: 4px;">
                                        <a href="https://maps.google.com/?q={{ $lat }},{{ $lon }}" style="color: #003a6c; font-size: 9px; font-weight: bold; text-decoration: underline;">
                                            Link Google Maps ({{ $lat }}, {{ $lon }})
                                        </a>
                                    </div>
                                @else
                                    <div style="height: 165px; line-height: 165px; background: #fafafa; border: 1px dashed #bdbdbd; color: #757575; font-size: 10px;">
                                        Peta Koordinat Kosong
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @elseif($totalPhotos === 4)
            <tr>
                <td colspan="2" style="padding: 6px 8px;">
                    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0;">
                        <!-- Baris 1: Foto 1 dan Foto 2 (Lebar 50% masing-masing, Tinggi 175px) -->
                        <tr>
                            <td style="width: 50%; padding: 0 4px 6px 0; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 175px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[0] }}" alt="Foto 1" style="width: 100%; height: 175px; object-fit: cover;">
                                </div>
                            </td>
                            <td style="width: 50%; padding: 0 0 6px 4px; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 175px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[1] }}" alt="Foto 2" style="width: 100%; height: 175px; object-fit: cover;">
                                </div>
                            </td>
                        </tr>
                        <!-- Baris 2: Foto 3, Foto 4, dan Peta Satelit (Lebar 33.3% masing-masing, Tinggi 160px) -->
                        <tr>
                            <td colspan="2" style="padding: 0; border: none;">
                                <table style="width: 100%; border-collapse: collapse; border: none; margin: 0;">
                                    <tr>
                                        <td style="width: 33.33%; padding: 0 4px 0 0; border: none; text-align: center; vertical-align: top;">
                                            <div style="height: 160px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                                <img src="{{ $photos[2] }}" alt="Foto 3" style="width: 100%; height: 160px; object-fit: cover;">
                                            </div>
                                        </td>
                                        <td style="width: 33.33%; padding: 0 2px 0 2px; border: none; text-align: center; vertical-align: top;">
                                            <div style="height: 160px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                                <img src="{{ $photos[3] }}" alt="Foto 4" style="width: 100%; height: 160px; object-fit: cover;">
                                            </div>
                                        </td>
                                        <td style="width: 33.33%; padding: 0 0 0 4px; border: none; text-align: center; vertical-align: top;">
                                            @if($mapBase64)
                                                <div class="map-container" style="width: 100%; height: 140px; position: relative; background: #eee; overflow: hidden; border-radius: 4px;">
                                                    <img src="{{ $mapBase64 }}" alt="Map" style="width: 100%; height: 140px; object-fit: cover;">
                                                    <div class="popup-box" style="top: 4px; padding: 3px 5px; width: 110px;">
                                                        <div class="popup-title" style="font-size: 9px;">{{ $asset->kode_aset }}</div>
                                                        <div class="popup-text" style="font-size: 8px;">{{ Str::limit($asset->alamat_namajalan, 20) }}</div>
                                                    </div>
                                                    <div class="marker-icon" style="top: 50px;">
                                                        <img src="{{ $markerBase64 }}" style="width: 20px;">
                                                    </div>
                                                </div>
                                                <div style="margin-top: 4px;">
                                                    <a href="https://maps.google.com/?q={{ $lat }},{{ $lon }}" style="color: #003a6c; font-size: 8.5px; font-weight: bold; text-decoration: underline;">
                                                        Link Google Maps ({{ $lat }}, {{ $lon }})
                                                    </a>
                                                </div>
                                            @else
                                                <div style="height: 160px; line-height: 160px; background: #fafafa; border: 1px dashed #bdbdbd; color: #757575; font-size: 10px;">
                                                    Peta Koordinat Kosong
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @else
            <tr>
                <td colspan="2" style="padding: 6px 8px;">
                    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0;">
                        <!-- Baris 1: 3 Foto Pertama (Lebar 33.3%, Tinggi 160px) -->
                        <tr>
                            <td style="width: 33.33%; padding: 0 4px 6px 0; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 160px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[0] }}" alt="Foto 1" style="width: 100%; height: 160px; object-fit: cover;">
                                </div>
                            </td>
                            <td style="width: 33.33%; padding: 0 2px 6px 2px; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 160px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[1] }}" alt="Foto 2" style="width: 100%; height: 160px; object-fit: cover;">
                                </div>
                            </td>
                            <td style="width: 33.33%; padding: 0 0 6px 4px; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 160px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[2] }}" alt="Foto 3" style="width: 100%; height: 160px; object-fit: cover;">
                                </div>
                            </td>
                        </tr>
                        <!-- Baris 2: Foto 4, Foto 5, dan Peta Satelit -->
                        <tr>
                            <td style="width: 33.33%; padding: 0 4px 0 0; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 160px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[3] }}" alt="Foto 4" style="width: 100%; height: 160px; object-fit: cover;">
                                </div>
                            </td>
                            <td style="width: 33.33%; padding: 0 2px 0 2px; border: none; text-align: center; vertical-align: top;">
                                <div style="height: 160px; overflow: hidden; background: #f0f0f0; border-radius: 4px;">
                                    <img src="{{ $photos[4] ?? $photos[0] }}" alt="Foto 5" style="width: 100%; height: 160px; object-fit: cover;">
                                </div>
                            </td>
                            <td style="width: 33.33%; padding: 0 0 0 4px; border: none; text-align: center; vertical-align: top;">
                                @if($mapBase64)
                                    <div class="map-container" style="width: 100%; height: 140px; position: relative; background: #eee; overflow: hidden; border-radius: 4px;">
                                        <img src="{{ $mapBase64 }}" alt="Map" style="width: 100%; height: 140px; object-fit: cover;">
                                        <div class="popup-box" style="top: 4px; padding: 3px 5px; width: 110px;">
                                            <div class="popup-title" style="font-size: 9px;">{{ $asset->kode_aset }}</div>
                                            <div class="popup-text" style="font-size: 8px;">{{ Str::limit($asset->alamat_namajalan, 20) }}</div>
                                        </div>
                                        <div class="marker-icon" style="top: 50px;">
                                            <img src="{{ $markerBase64 }}" style="width: 20px;">
                                        </div>
                                    </div>
                                    <div style="margin-top: 4px;">
                                        <a href="https://maps.google.com/?q={{ $lat }},{{ $lon }}" style="color: #003a6c; font-size: 8.5px; font-weight: bold; text-decoration: underline;">
                                            Link Google Maps ({{ $lat }}, {{ $lon }})
                                        </a>
                                    </div>
                                @else
                                    <div style="height: 160px; line-height: 160px; background: #fafafa; border: 1px dashed #bdbdbd; color: #757575; font-size: 10px;">
                                        Peta Koordinat Kosong
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        @endif

        <!-- Informasi Detail Aset -->
        <tr>
            <td colspan="2" style="padding: 15px;">
                <table class="info-table no-border-table">
                    <tr>
                        <td class="info-label">Aset Properti</td>
                        <td class="info-colon">:</td>
                        <td>{{ $asset->jenis_aset }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">PRK</td>
                        <td class="info-colon">:</td>
                        <td>{{ $asset->kode_aset }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Koordinat</td>
                        <td class="info-colon">:</td>
                        <td>{{ $asset->koordinat_latitude ?? '-' }}, {{ $asset->koordinat_longitude ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Dokumen</td>
                        <td class="info-colon">:</td>
                        <td>{{ $asset->jenis_bukti_kepemilikan ?? '-' }}, {{ $asset->nomor_bukti_kepemilikan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">LT/LB</td>
                        <td class="info-colon">:</td>
                        <td>{{ rtrim(rtrim(number_format($asset->luas_tanah ?? 0, 2, ',', '.'), '0'), ',') }} / {{ rtrim(rtrim(number_format($asset->luas_bangunan ?? 0, 2, ',', '.'), '0'), ',') }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Bank Asal</td>
                        <td class="info-colon">:</td>
                        <td>{{ $asset->bank_asal ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Wakil Kerja</td>
                        <td class="info-colon">:</td>
                        <td>{{ $asset->wakil_kerja ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Permasalahan</td>
                        <td class="info-colon">:</td>
                        <td>{{ $asset->permasalahan ?? '-' }}</td>
                    </tr>
                </table>

                <!-- Batas-batas dan Keterangan Tambahan -->
                <table class="batas-table no-border-table">
                    <tr class="batas-header">
                        <td colspan="3">Batas-batas</td>
                        <td colspan="3">Keterangan Tambahan</td>
                    </tr>
                    <tr>
                        <td class="col-batas-label">Utara</td>
                        <td class="col-batas-colon">:</td>
                        <td class="col-batas-value">{{ $asset->batas_utara ?? '-' }}</td>
                        
                        <td class="col-ket-label">Kondisi</td>
                        <td class="col-ket-colon">:</td>
                        <td class="col-ket-value">
                            {{ $asset->kondisi_aset }}
                            @if($asset->kondisi_aset == 'LAIN-LAIN' && $asset->kondisi_aset_lainlain)
                                ({{ $asset->kondisi_aset_lainlain }})
                            @elseif($asset->kondisi_aset == 'DISEWAKAN' && $asset->sewa_lelang_nama_pihak)
                                (Penyewa: {{ $asset->sewa_lelang_nama_pihak }})
                            @elseif($asset->kondisi_aset == 'DILELANG' && $asset->sewa_lelang_nama_pihak)
                                (Pemenang: {{ $asset->sewa_lelang_nama_pihak }})
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="col-batas-label">Timur</td>
                        <td class="col-batas-colon">:</td>
                        <td class="col-batas-value">{{ $asset->batas_timur ?? '-' }}</td>
                        
                        <td class="col-ket-label">Potensi Aset</td>
                        <td class="col-ket-colon">:</td>
                        <td class="col-ket-value">{{ $asset->potensi_aset ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-batas-label">Selatan</td>
                        <td class="col-batas-colon">:</td>
                        <td class="col-batas-value">{{ $asset->batas_selatan ?? '-' }}</td>
                        
                        <td class="col-ket-label">Update Terakhir</td>
                        <td class="col-ket-colon">:</td>
                        <td class="col-ket-value">{{ $asset->tanggal_update_kondisi ? \Carbon\Carbon::parse($asset->tanggal_update_kondisi)->format('d-m-Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="col-batas-label">Barat</td>
                        <td class="col-batas-colon">:</td>
                        <td class="col-batas-value">{{ $asset->batas_barat ?? '-' }}</td>
                        
                        <td class="col-ket-label">Papan Nama</td>
                        <td class="col-ket-colon">:</td>
                        <td class="col-ket-value">{{ $asset->papan_nama ? 'Ada' : 'Tidak Ada' }}</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                        <td class="col-ket-label">Keterangan</td>
                        <td class="col-ket-colon">:</td>
                        <td class="col-ket-value">{{ $asset->keterangan_kondisi_aset ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
    @endforeach
</body>
</html>
