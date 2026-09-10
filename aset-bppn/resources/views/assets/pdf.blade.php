<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Pelaksanaan Pemeliharaan dan Pengamanan Aset Properti</title>
    <style>
        @page {
            size: legal landscape;
            margin: 8mm 6mm 10mm 6mm;
        }
        body {
            font-family: Aptos, "Segoe UI", Arial, DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #000;
            line-height: 1.2;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 12px;
            text-transform: uppercase;
            line-height: 1.3;
        }
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            table-layout: fixed;
        }
        table.report-table th, table.report-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 8px;
            line-height: 1.15;
            vertical-align: middle;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        table.report-table thead th {
            text-align: center;
            font-weight: bold;
            background-color: #cbc9c9ff;
            text-transform: uppercase;
        }
        .text-left {
            text-align: left !important;
        }
        .text-center {
            text-align: center !important;
        }
        .checkmark {
            font-family: DejaVu Sans, sans-serif;
            font-weight: bold;
            font-size: 8px;
        }
        .signature-container {
            float: right;
            margin-top: 20px;
            text-align: center;
            width: 220px;
            page-break-inside: avoid;
        }
        .signature-title {
            font-size: 8px;
            margin-bottom: 1px;
        }
        .signature-name {
            font-size: 8px;
            font-weight: bold;
            margin-top: 45px;
        }
    </style>
</head>
<body>

    <div class="header-title">
        LAPORAN REKAPITULASI PELAKSANAAN PEMELIHARAAN DAN PENGAMANAN<br>
        ASET PROPERTI EKS BPPN DAN EKS KELOLAAN PT. PPA (Persero)<br>
        PERIODE SEMESTER {{ $semesterRoman ?? 'I' }} TAHUN {{ $tahun ?? date('Y') }}
    </div>

    <table class="report-table">
        <colgroup>
            <col style="width:2.5%">
            <col style="width:5.4%">
            <col style="width:5.7%">
            <col style="width:7.2%">
            <col style="width:17.5%">
            <col style="width:4.9%">
            <col style="width:6%">
            <col style="width:2.5%">
            <col style="width:2.5%">
            <col style="width:4.2%">
            <col style="width:6.1%">
            <col style="width:5.2%">
            <col style="width:3.7%">
            <col style="width:3.7%">
            <col style="width:5.3%">
            <col style="width:6.7%">
            <col style="width:10.9%">
        </colgroup>
        <thead>
            <tr style="height:0; line-height:0; border:none; padding:0; margin:0; overflow:hidden;">
                <th style="width:2.5%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:5.4%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:5.7%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:7.2%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:17.5%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:4.9%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:6%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:2.5%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:2.5%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:4.2%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:6.1%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:5.2%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:3.7%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:3.7%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:5.3%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:6.7%; border:none; padding:0; margin:0; height:0;"></th>
                <th style="width:10.9%; border:none; padding:0; margin:0; height:0;"></th>
            </tr>
            <tr>
                <th rowspan="2">NO</th>
                <th rowspan="2">KPKNL</th>
                <th colspan="9">DATA ASET</th>
                <th colspan="3">KONDISI ASET</th>
                <th rowspan="2">POTENSI ASET</th>
                <th rowspan="2">PEMASANGAN PAPAN NAMA</th>
                <th rowspan="2">KETERANGAN KONDISI ASET</th>
            </tr>
            <tr>
                <!-- DATA ASET -->
                <th>Nama Waker</th>
                <th>NOMOR ASET</th>
                <th>ALAMAT</th>
                <th>JENIS BUKTI KEPEMILIKAN</th>
                <th>NOMOR BUKTI KEPEMILIKAN</th>
                <th>LT (m²)</th>
                <th>LB (m²)</th>
                <th>JENIS</th>
                <th>NJOP</th>
                <!-- KONDISI ASET -->
                <th>DIHUNI / DIGUNAKAN PIHAK KETIGA</th>
                <th>KOSONG</th>
                <th>LAIN-LAIN</th>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
                <th>10</th>
                <th>11</th>
                <th>12</th>
                <th>13</th>
                <th>14</th>
                <th>15</th>
                <th>16</th>
                <th>17</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $index => $asset)
            @php
                $kondisiUpper = strtoupper(trim($asset->kondisi_aset ?? ''));
                $isDihuniPihakKetiga = in_array($kondisiUpper, ['DIHUNI', 'DIGUNAKAN PIHAK KETIGA']);
                $isKosong = ($kondisiUpper === 'KOSONG');
                $isLainlain = ($kondisiUpper === 'LAIN-LAIN');
                
                $alamatFull = $asset->alamat_namajalan ?? '';
                if ($asset->village) { $alamatFull .= ', ' . $asset->village->name; }
                if ($asset->district) { $alamatFull .= ', ' . $asset->district->name; }
                if ($asset->regency) { $alamatFull .= ', ' . $asset->regency->name; }
                if ($asset->province) { $alamatFull .= ', ' . $asset->province->name; }
                if ($asset->kodepos) { $alamatFull .= ' ' . $asset->kodepos; }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">KPKNL Palembang</td>
                <td class="text-left">{{ $asset->wakil_kerja ?? '-' }}</td>
                <td class="text-center"><strong>{{ $asset->kode_aset }}</strong></td>
                <td class="text-left">{{ $alamatFull }}</td>
                <td class="text-center">{{ $asset->jenis_bukti_kepemilikan ?? '-' }}</td>
                <td class="text-center">{{ $asset->nomor_bukti_kepemilikan ?? '-' }}</td>
                <td class="text-center">{{ $asset->luas_tanah ? number_format($asset->luas_tanah, 0, ',', '.') : '-' }}</td>
                <td class="text-center">{{ $asset->luas_bangunan ? number_format($asset->luas_bangunan, 0, ',', '.') : '-' }}</td>
                <td class="text-center">{{ $asset->jenis_aset }}</td>
                <td class="text-center">{{ $asset->njop ? 'Rp ' . number_format($asset->njop, 0, ',', '.') : '-' }}</td>
                <td class="text-center checkmark">{!! $isDihuniPihakKetiga ? '√' : '' !!}</td>
                <td class="text-center checkmark">{!! $isKosong ? '√' : '' !!}</td>
                <td class="text-center">{!! $isLainlain ? ($asset->kondisi_aset_lainlain ?? '√') : '' !!}</td>
                <td class="text-left">{{ $asset->potensi_aset ?? '-' }}</td>
                <td class="text-center">{{ $asset->papan_nama ? 'Sudah' : 'Belum' }}</td>
                <td class="text-left">{{ $asset->keterangan_kondisi_aset ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="17" class="text-center" style="padding: 20px;">Belum ada data aset untuk periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-container">
        <div class="signature-title">Mengetahui,</div>
        <div class="signature-title" style="font-weight: bold;">Kepala KPKNL Palembang</div>
        <div class="signature-name">Mardhanus Rudiyanto</div>
    </div>

</body>
</html>
