<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Resume Profil Aset</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 15mm 10mm 15mm 10mm;
        }
        body {
            font-family: Aptos, "Segoe UI", Arial, DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #000;
            line-height: 1.2;
        }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table.resume-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 4px;
        }
        table.resume-table th,
        table.resume-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            font-size: 8px;
            line-height: 1.2;
            vertical-align: middle;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        table.resume-table thead th {
            text-align: center;
            font-weight: bold;
            background-color: #d9d9d9;
            text-transform: uppercase;
        }
        table.resume-table td {
            vertical-align: top;
        }
        .group-header td {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 8px;
            padding: 3px 4px;
        }
        .text-center { text-align: center !important; }
        .text-right  { text-align: right  !important; }
        .text-left   { text-align: left   !important; }
    </style>
</head>
<body>

    <div class="doc-title">Resume profil aset</div>

    <table class="resume-table">
        <colgroup>
            <col style="width:4.77%">
            <col style="width:11.47%">
            <col style="width:8.79%">
            <col style="width:22.30%">
            <col style="width:10.64%">
            <col style="width:10.84%">
            <col style="width:7%">
            <col style="width:7%">
            <col style="width:17.19%">
        </colgroup>
        <thead>
            {{-- Dummy row to force DOMPDF to respect col widths --}}
            <tr style="height:0; line-height:0;">
                <th style="width:4.77%;   border:none; padding:0; height:0;"></th>
                <th style="width:11.47%;  border:none; padding:0; height:0;"></th>
                <th style="width:8.79%;   border:none; padding:0; height:0;"></th>
                <th style="width:22.30%;  border:none; padding:0; height:0;"></th>
                <th style="width:10.64%;  border:none; padding:0; height:0;"></th>
                <th style="width:10.84%;  border:none; padding:0; height:0;"></th>
                <th style="width:7%;      border:none; padding:0; height:0;"></th>
                <th style="width:7%;      border:none; padding:0; height:0;"></th>
                <th style="width:17.19%;  border:none; padding:0; height:0;"></th>
            </tr>
            <tr>
                <th rowspan="2" class="text-center">No.</th>
                <th rowspan="2" class="text-center">KODE ASET</th>
                <th rowspan="2" class="text-center">JENIS</th>
                <th rowspan="2" class="text-center">LOKASI ASET</th>
                <th rowspan="2" class="text-center">DOKUMEN ASET</th>
                <th rowspan="2" class="text-center">NO. DOKUMEN</th>
                <th class="text-center">LT</th>
                <th class="text-center">LB</th>
                <th rowspan="2" class="text-center">KETERANGAN</th>
            </tr>
            <tr>
                <th class="text-center">(m²)</th>
                <th class="text-center">(m²)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupLabels = range('A', 'Z');
                $groupIdx    = 0;
            @endphp

            @forelse ($grouped as $kotaKab => $asets)
                {{-- Baris sub-judul grup --}}
                <tr class="group-header">
                    <td colspan="9">{{ $groupLabels[$groupIdx++] ?? '-' }}. {{ $kotaKab }}</td>
                </tr>

                @foreach ($asets as $i => $asset)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}.</td>
                        <td class="text-left">{{ $asset->kode_aset ?? '-' }}</td>
                        <td class="text-center">{{ $asset->jenis_aset ?? '-' }}</td>
                        <td class="text-left">
                            @php
                                $alamatFull = [];
                                if (!empty($asset->alamat_namajalan)) $alamatFull[] = $asset->alamat_namajalan;
                                if (!empty($asset->alamat_rt_rw)) $alamatFull[] = "RT/RW " . $asset->alamat_rt_rw;
                                if (!empty($asset->village->name)) $alamatFull[] = "Kel/Desa " . $asset->village->name;
                                if (!empty($asset->district->name)) $alamatFull[] = "Kec. " . $asset->district->name;
                                if (!empty($asset->regency->name)) $alamatFull[] = $asset->regency->name;
                            @endphp
                            {{ !empty($alamatFull) ? implode(', ', $alamatFull) : '-' }}
                        </td>
                        <td class="text-center">{{ $asset->jenis_bukti_kepemilikan ?? '-' }}</td>
                        <td class="text-left">{{ $asset->nomor_bukti_kepemilikan ?? '-' }}</td>
                        <td class="text-right">
                            {{ $asset->luas_tanah ? number_format($asset->luas_tanah, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right">
                            {{ $asset->luas_bangunan ? number_format($asset->luas_bangunan, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-left">{{ $asset->keterangan_kondisi_aset ?? '-' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 10px;">Tidak ada data aset.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
