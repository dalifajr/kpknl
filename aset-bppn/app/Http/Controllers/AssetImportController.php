<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetLease;
use App\Models\AssetImport;
use App\Models\AssetImportItem;
use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Exports\AssetImportTemplateExport;
use App\Exports\FailedImportExport;
use App\Imports\AssetsPreviewImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AssetImportController extends Controller
{
    /**
     * Display import dashboard & history list
     */
    public function index()
    {
        $imports = AssetImport::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalImports = AssetImport::count();
        $totalImportedAssets = AssetImport::sum('success_rows');
        $totalFailedAssets = AssetImport::sum('failed_rows');

        return view('assets.import.index', compact('imports', 'totalImports', 'totalImportedAssets', 'totalFailedAssets'));
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $filename = 'template_impor_data_aset_eks_bppn.xlsx';
        $storagePath = 'templates/' . $filename;
        
        Excel::store(new AssetImportTemplateExport, $storagePath, 'public');
        $fullPath = storage_path('app/public/' . $storagePath);

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Parse uploaded file and display Preview with Column & Row validation
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'Silakan pilih berkas Excel atau CSV untuk diimpor.',
            'file.mimes' => 'Format berkas harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran berkas maksimal 10 MB.',
        ]);

        $file = $request->file('file');
        $originalFileName = $file->getClientOriginalName();

        // Move file to temporary directory in storage
        $targetDir = storage_path('app/imports');
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $tempFileName = 'import_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $fullPath = $targetDir . DIRECTORY_SEPARATOR . $tempFileName;
        $file->move($targetDir, $tempFileName);

        try {
            $data = Excel::toArray(new AssetsPreviewImport, $fullPath);
            
            // Clean up temporary stored file
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }

            if (empty($data) || empty($data[0])) {
                return redirect()->back()->with('error', 'Berkas Excel kosong atau tidak memiliki data baris.');
            }

            $rawRows = $data[0];
            $processedRows = [];
            $existingKodeAssets = Asset::pluck('kode_aset')->map(fn($k) => strtoupper(trim($k)))->toArray();
            $seenKodeInFile = [];

            $totalValid = 0;
            $totalWarning = 0;
            $totalError = 0;

            foreach ($rawRows as $index => $row) {
                $rowNumber = $index + 2; // header is row 1
                $kodeAset = trim((string)($row['kode_aset'] ?? ''));
                $errors = [];
                $warnings = [];

                if (empty($kodeAset)) {
                    $errors[] = 'Kode Aset wajib diisi.';
                } else {
                    $upperKode = strtoupper($kodeAset);
                    if (in_array($upperKode, $existingKodeAssets)) {
                        $errors[] = "Kode Aset '{$kodeAset}' sudah terdaftar di database.";
                    }
                    if (in_array($upperKode, $seenKodeInFile)) {
                        $errors[] = "Kode Aset '{$kodeAset}' duplikat di dalam berkas Excel ini.";
                    }
                    $seenKodeInFile[] = $upperKode;
                }

                // Region validation check
                $alamatProvinsi = trim((string)($row['alamat_provinsi'] ?? ''));
                $alamatKota = trim((string)($row['alamat_kota_kab'] ?? ''));
                if (!empty($alamatProvinsi)) {
                    $prov = Province::where('name', 'LIKE', "%{$alamatProvinsi}%")->first();
                    if (!$prov) {
                        $warnings[] = "Nama Provinsi '{$alamatProvinsi}' tidak cocok persis di database wilayah.";
                    }
                }
                if (!empty($alamatKota)) {
                    $reg = Regency::where('name', 'LIKE', "%{$alamatKota}%")->first();
                    if (!$reg) {
                        $warnings[] = "Nama Kota/Kab '{$alamatKota}' tidak cocok persis di database wilayah.";
                    }
                }

                // Date format checks
                $dateCols = ['tanggal_update_kondisi', 'sewa_lelang_tgl_mulai', 'sewa_lelang_tgl_selesai'];
                foreach ($dateCols as $dc) {
                    if (!empty($row[$dc])) {
                        try {
                            $parsedDate = $this->parseDateValue($row[$dc]);
                            if (!$parsedDate) {
                                $warnings[] = "Format tanggal '{$dc}' ('{$row[$dc]}') mungkin tidak sesuai standar.";
                            }
                        } catch (\Exception $e) {
                            $warnings[] = "Tanggal '{$dc}' tidak valid.";
                        }
                    }
                }

                $status = 'valid';
                if (!empty($errors)) {
                    $status = 'error';
                    $totalError++;
                } elseif (!empty($warnings)) {
                    $status = 'warning';
                    $totalWarning++;
                } else {
                    $totalValid++;
                }

                $processedRows[] = [
                    'row_number' => $rowNumber,
                    'status' => $status,
                    'errors' => $errors,
                    'warnings' => $warnings,
                    'data' => $row,
                ];
            }

            $previewToken = (string) Str::uuid();
            Session::put('asset_import_preview_' . $previewToken, [
                'file_name' => $originalFileName,
                'total_rows' => count($processedRows),
                'rows' => $processedRows,
                'created_at' => now(),
            ]);

            return view('assets.import.preview', [
                'previewToken' => $previewToken,
                'fileName' => $originalFileName,
                'rows' => $processedRows,
                'totalRows' => count($processedRows),
                'totalValid' => $totalValid,
                'totalWarning' => $totalWarning,
                'totalError' => $totalError,
            ]);

        } catch (\Exception $e) {
            if (isset($fullPath) && file_exists($fullPath)) {
                @unlink($fullPath);
            }
            return redirect()->back()->with('error', 'Gagal memproses berkas Excel: ' . $e->getMessage());
        }
    }

    /**
     * Execute Import for Selected Rows & Columns
     */
    public function execute(Request $request)
    {
        $previewToken = $request->input('preview_token');
        $sessionKey = 'asset_import_preview_' . $previewToken;
        $previewData = Session::get($sessionKey);

        if (!$previewData || empty($previewData['rows'])) {
            return redirect()->route('assets.import.index')->with('error', 'Sesi pratinjau impor telah kadaluarsa. Silakan unggah kembali berkas Anda.');
        }

        $selectedRows = $request->input('selected_rows', []);
        $selectedColumns = $request->input('selected_columns', []);

        if (empty($selectedRows)) {
            return redirect()->back()->with('error', 'Silakan pilih setidaknya satu baris data untuk diimpor.');
        }

        $allRows = $previewData['rows'];
        $fileName = $previewData['file_name'];

        $import = AssetImport::create([
            'file_name' => $fileName,
            'user_id' => auth()->id(),
            'total_rows' => count($selectedRows),
            'success_rows' => 0,
            'failed_rows' => 0,
            'status' => 'pending',
            'selected_columns' => $selectedColumns,
        ]);

        $successCount = 0;
        $failedCount = 0;

        foreach ($allRows as $rowItem) {
            $rowNum = (int)$rowItem['row_number'];
            if (!in_array($rowNum, $selectedRows)) {
                continue;
            }

            $rawRow = $rowItem['data'];
            $kodeAset = trim((string)($rawRow['kode_aset'] ?? ''));

            try {
                DB::beginTransaction();

                $assetData = [];
                $allowedColumns = empty($selectedColumns) ? array_keys($rawRow) : $selectedColumns;

                // Map standard fields
                $fieldMapping = [
                    'kode_aset' => 'kode_aset',
                    'bank_asal' => 'bank_asal',
                    'jenis_aset' => 'jenis_aset',
                    'permasalahan' => 'permasalahan',
                    'jenis_bukti_kepemilikan' => 'jenis_bukti_kepemilikan',
                    'nomor_bukti_kepemilikan' => 'nomor_bukti_kepemilikan',
                    'luas_tanah' => 'luas_tanah',
                    'luas_bangunan' => 'luas_bangunan',
                    'njop' => 'njop',
                    'alamat_namajalan' => 'alamat_namajalan',
                    'alamat_rt_rw' => 'alamat_rt_rw',
                    'kodepos' => 'kodepos',
                    'alamatlama_namajalan' => 'alamatlama_namajalan',
                    'alamatlama_rt_rw' => 'alamatlama_rt_rw',
                    'alamatlama_kodepos' => 'alamatlama_kodepos',
                    'batas_utara' => 'batas_utara',
                    'batas_timur' => 'batas_timur',
                    'batas_selatan' => 'batas_selatan',
                    'batas_barat' => 'batas_barat',
                    'koordinat_latitude' => 'koordinat_latitude',
                    'koordinat_longitude' => 'koordinat_longitude',
                    'kondisi_aset' => 'kondisi_aset',
                    'kondisi_aset_lainlain' => 'kondisi_aset_lainlain',
                    'potensi_aset' => 'potensi_aset',
                    'wakil_kerja' => 'wakil_kerja',
                    'keterangan_kondisi_aset' => 'keterangan_kondisi_aset',
                    'semester' => 'semester',
                    'tahun' => 'tahun',
                    'sewa_lelang_nama_pihak' => 'sewa_lelang_nama_pihak',
                    'sewa_lelang_no_surat' => 'sewa_lelang_no_surat',
                    'sewa_lelang_nilai' => 'sewa_lelang_nilai',
                ];

                foreach ($fieldMapping as $excelCol => $dbCol) {
                    if (in_array($excelCol, $allowedColumns) && isset($rawRow[$excelCol])) {
                        $val = trim((string)$rawRow[$excelCol]);
                        $assetData[$dbCol] = $val !== '' ? $val : null;
                    }
                }

                // Boolean Papan Nama
                if (in_array('papan_nama', $allowedColumns)) {
                    $pn = strtolower(trim((string)($rawRow['papan_nama'] ?? '')));
                    $assetData['papan_nama'] = in_array($pn, ['ya', '1', 'ada', 'true', 'yes']);
                }

                // Dates mapping
                if (in_array('tanggal_update_kondisi', $allowedColumns) && !empty($rawRow['tanggal_update_kondisi'])) {
                    $assetData['tanggal_update_kondisi'] = $this->parseDateValue($rawRow['tanggal_update_kondisi']);
                }
                if (in_array('sewa_lelang_tgl_mulai', $allowedColumns) && !empty($rawRow['sewa_lelang_tgl_mulai'])) {
                    $assetData['sewa_lelang_tgl_mulai'] = $this->parseDateValue($rawRow['sewa_lelang_tgl_mulai']);
                }
                if (in_array('sewa_lelang_tgl_selesai', $allowedColumns) && !empty($rawRow['sewa_lelang_tgl_selesai'])) {
                    $assetData['sewa_lelang_tgl_selesai'] = $this->parseDateValue($rawRow['sewa_lelang_tgl_selesai']);
                }

                // Region ID Resolution (Alamat Baru)
                if (in_array('alamat_provinsi', $allowedColumns) && !empty($rawRow['alamat_provinsi'])) {
                    $prov = Province::where('name', 'LIKE', '%' . trim($rawRow['alamat_provinsi']) . '%')->first();
                    if ($prov) {
                        $assetData['alamat_provinsi_id'] = $prov->id;
                        if (in_array('alamat_kota_kab', $allowedColumns) && !empty($rawRow['alamat_kota_kab'])) {
                            $reg = Regency::where('province_id', $prov->id)->where('name', 'LIKE', '%' . trim($rawRow['alamat_kota_kab']) . '%')->first();
                            if ($reg) {
                                $assetData['alamat_kota_kab_id'] = $reg->id;
                                if (in_array('alamat_kecamatan', $allowedColumns) && !empty($rawRow['alamat_kecamatan'])) {
                                    $dist = District::where('regency_id', $reg->id)->where('name', 'LIKE', '%' . trim($rawRow['alamat_kecamatan']) . '%')->first();
                                    if ($dist) {
                                        $assetData['alamat_kecamatan_id'] = $dist->id;
                                        if (in_array('alamat_kelurahan', $allowedColumns) && !empty($rawRow['alamat_kelurahan'])) {
                                            $vill = Village::where('district_id', $dist->id)->where('name', 'LIKE', '%' . trim($rawRow['alamat_kelurahan']) . '%')->first();
                                            if ($vill) {
                                                $assetData['alamat_kelurahan_id'] = $vill->id;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Region ID Resolution (Alamat Lama)
                if (in_array('alamatlama_provinsi', $allowedColumns) && !empty($rawRow['alamatlama_provinsi'])) {
                    $oldProv = Province::where('name', 'LIKE', '%' . trim($rawRow['alamatlama_provinsi']) . '%')->first();
                    if ($oldProv) {
                        $assetData['alamatlama_provinsi_id'] = $oldProv->id;
                        if (in_array('alamatlama_kota_kab', $allowedColumns) && !empty($rawRow['alamatlama_kota_kab'])) {
                            $oldReg = Regency::where('province_id', $oldProv->id)->where('name', 'LIKE', '%' . trim($rawRow['alamatlama_kota_kab']) . '%')->first();
                            if ($oldReg) {
                                $assetData['alamatlama_kota_kab_id'] = $oldReg->id;
                                if (in_array('alamatlama_kecamatan', $allowedColumns) && !empty($rawRow['alamatlama_kecamatan'])) {
                                    $oldDist = District::where('regency_id', $oldReg->id)->where('name', 'LIKE', '%' . trim($rawRow['alamatlama_kecamatan']) . '%')->first();
                                    if ($oldDist) {
                                        $assetData['alamatlama_kecamatan_id'] = $oldDist->id;
                                        if (in_array('alamatlama_kelurahan', $allowedColumns) && !empty($rawRow['alamatlama_kelurahan'])) {
                                            $oldVill = Village::where('district_id', $oldDist->id)->where('name', 'LIKE', '%' . trim($rawRow['alamatlama_kelurahan']) . '%')->first();
                                            if ($oldVill) {
                                                $assetData['alamatlama_kelurahan_id'] = $oldVill->id;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Coordinate Google Link
                if (!empty($assetData['koordinat_latitude']) && !empty($assetData['koordinat_longitude'])) {
                    $assetData['koordinat_link'] = "https://maps.google.com/?q={$assetData['koordinat_latitude']},{$assetData['koordinat_longitude']}";
                }

                // Check required kode_aset again
                if (empty($assetData['kode_aset'])) {
                    throw new \Exception('Kolom Kode Aset tidak boleh kosong.');
                }

                // Create Asset Record
                $createdAsset = Asset::create($assetData);

                // Create Lease Record if DISEWAKAN
                if (strtoupper($createdAsset->kondisi_aset ?? '') === 'DISEWAKAN' && $createdAsset->sewa_lelang_nama_pihak && $createdAsset->sewa_lelang_tgl_mulai && $createdAsset->sewa_lelang_tgl_selesai) {
                    AssetLease::create([
                        'asset_id' => $createdAsset->id,
                        'nama_penyewa' => $createdAsset->sewa_lelang_nama_pihak,
                        'no_surat' => $createdAsset->sewa_lelang_no_surat,
                        'tgl_mulai' => $createdAsset->sewa_lelang_tgl_mulai,
                        'tgl_selesai' => $createdAsset->sewa_lelang_tgl_selesai,
                        'nilai_sewa' => $createdAsset->sewa_lelang_nilai ?? 0,
                        'keterangan' => 'Impor Masal Excel',
                        'user_id' => auth()->id(),
                    ]);
                }

                AssetImportItem::create([
                    'asset_import_id' => $import->id,
                    'row_number' => $rowNum,
                    'kode_aset' => $kodeAset,
                    'raw_data' => $rawRow,
                    'status' => 'success',
                    'created_asset_id' => $createdAsset->id,
                ]);

                DB::commit();
                $successCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $failedCount++;

                AssetImportItem::create([
                    'asset_import_id' => $import->id,
                    'row_number' => $rowNum,
                    'kode_aset' => $kodeAset,
                    'raw_data' => $rawRow,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        $importStatus = 'completed';
        if ($failedCount > 0 && $successCount > 0) {
            $importStatus = 'partial';
        } elseif ($failedCount > 0 && $successCount === 0) {
            $importStatus = 'failed';
        }

        $import->update([
            'success_rows' => $successCount,
            'failed_rows' => $failedCount,
            'status' => $importStatus,
        ]);

        activity()
            ->performedOn($import)
            ->causedBy(auth()->user())
            ->log("melakukan impor masal data aset: {$successCount} berhasil, {$failedCount} gagal dari total {$import->total_rows} baris.");

        Session::forget($sessionKey);

        return redirect()->route('assets.import.show', $import->id)
            ->with('success', "Proses impor selesai: {$successCount} berhasil, {$failedCount} gagal.");
    }

    /**
     * Display Single Import Session Details & Failed/Success Item Breakdown
     */
    public function show(Request $request, $id)
    {
        $import = AssetImport::with(['user', 'items' => function($q) {
            $q->orderBy('row_number', 'asc');
        }])->findOrFail($id);

        $filterStatus = $request->query('status');
        $itemsQuery = $import->items();
        if ($filterStatus && in_array($filterStatus, ['success', 'failed'])) {
            $itemsQuery->where('status', $filterStatus);
        }
        $items = $itemsQuery->paginate(15)->withQueryString();

        return view('assets.import.show', compact('import', 'items', 'filterStatus'));
    }

    /**
     * Download Excel of Failed Rows for a given Import Session
     */
    public function downloadFailed($id)
    {
        $import = AssetImport::findOrFail($id);
        if ($import->failed_rows <= 0) {
            return redirect()->back()->with('error', 'Tidak ada baris data gagal pada sesi impor ini.');
        }

        $filename = 'data_gagal_impor_batch_' . $import->id . '_' . date('Ymd_His') . '.xlsx';
        $storagePath = 'failed_imports/' . $filename;

        Excel::store(new FailedImportExport($import), $storagePath, 'public');
        $fullPath = storage_path('app/public/' . $storagePath);

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Helper to safely parse dates from Excel
     */
    private function parseDateValue($val)
    {
        if (empty($val)) return null;
        if (is_numeric($val)) {
            // Excel numeric date serial
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val))->format('Y-m-d');
        }
        return Carbon::parse($val)->format('Y-m-d');
    }
}
