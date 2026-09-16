<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserImportService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UserImportController extends Controller
{
    protected UserImportService $importService;

    public function __construct(UserImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Download Excel (.xlsx) Import Template
     */
    public function downloadTemplate()
    {
        $filename = 'template_import_user_sso.xlsx';

        $typesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';

        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

        $workbookRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="1">
    <font><sz val="11"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>
  </fonts>
  <fills count="2">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/><diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
  </cellXfs>
</styleSheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Template Import User" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';

        $sheet1Xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <dimension ref="A1:F3"/>
  <sheetViews>
    <sheetView tabSelected="1" workbookViewId="0"/>
  </sheetViews>
  <sheetFormatPr defaultRowHeight="15"/>
  <sheetData>
    <row r="1">
      <c r="A1" t="inlineStr"><is><t>name</t></is></c>
      <c r="B1" t="inlineStr"><is><t>username</t></is></c>
      <c r="C1" t="inlineStr"><is><t>email</t></is></c>
      <c r="D1" t="inlineStr"><is><t>role</t></is></c>
      <c r="E1" t="inlineStr"><is><t>status</t></is></c>
      <c r="F1" t="inlineStr"><is><t>password</t></is></c>
    </row>
    <row r="2">
      <c r="A2" t="inlineStr"><is><t>Budi Santoso</t></is></c>
      <c r="B2" t="inlineStr"><is><t>budi.santoso</t></is></c>
      <c r="C2" t="inlineStr"><is><t>budi@kpknl.go.id</t></is></c>
      <c r="D2" t="inlineStr"><is><t>user</t></is></c>
      <c r="E2" t="inlineStr"><is><t>active</t></is></c>
      <c r="F2" t="inlineStr"><is><t>Password123!</t></is></c>
    </row>
    <row r="3">
      <c r="A3" t="inlineStr"><is><t>Dewi Sartika</t></is></c>
      <c r="B3" t="inlineStr"><is><t>dewi.sartika</t></is></c>
      <c r="C3" t="inlineStr"><is><t>dewi@kpknl.go.id</t></is></c>
      <c r="D3" t="inlineStr"><is><t>admin</t></is></c>
      <c r="E3" t="inlineStr"><is><t>active</t></is></c>
      <c r="F3" t="inlineStr"><is><t>Password123!</t></is></c>
    </row>
  </sheetData>
</worksheet>';

        if (class_exists('ZipArchive')) {
            $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
            $zip = new \ZipArchive();
            if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                $zip->addFromString('[Content_Types].xml', $typesXml);
                $zip->addFromString('_rels/.rels', $relsXml);
                $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXml);
                $zip->addFromString('xl/workbook.xml', $workbookXml);
                $zip->addFromString('xl/styles.xml', $stylesXml);
                $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1Xml);
                $zip->close();
            }
            $xlsxBinary = file_get_contents($tempFile);
            @unlink($tempFile);
        } else {
            $writer = new \App\Services\SimpleZipWriter();
            $writer->addFile('[Content_Types].xml', $typesXml);
            $writer->addFile('_rels/.rels', $relsXml);
            $writer->addFile('xl/_rels/workbook.xml.rels', $workbookRelsXml);
            $writer->addFile('xl/workbook.xml', $workbookXml);
            $writer->addFile('xl/styles.xml', $stylesXml);
            $writer->addFile('xl/worksheets/sheet1.xml', $sheet1Xml);
            $xlsxBinary = $writer->getZipContent();
        }

        ActivityLogService::log(
            'import_template_downloaded',
            'Superadmin mengunduh Template Excel (.xlsx) Import User SSO'
        );

        return Response::make($xlsxBinary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }





    /**
     * Handle File Upload & Initialize Import Job
     */
    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'File Excel / CSV / XML wajib diunggah.',
            ], 422);
        }

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['csv', 'txt', 'xlsx', 'xls', 'xml'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format file tidak didukung (harus .csv, .xlsx, .xls, .xml, atau .txt).',
            ], 422);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'message' => 'Ukuran file maksimal 5 MB.',
            ], 422);
        }

        $filePath = $file->path() ?: ($file->getRealPath() ?: $file->getPathname());
        if (empty($filePath) || !file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file fisik yang diunggah dari server lokal.',
            ], 422);
        }

        try {
            $rows = $this->importService->parseFile($filePath);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memparsing isi file: ' . $e->getMessage(),
            ], 422);
        }

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'File Excel/CSV/XML kosong atau format baris data tidak dikenali.',
            ], 422);
        }

        try {
            $validation = $this->importService->validateRows($rows);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memvalidasi baris data: ' . $e->getMessage(),
            ], 422);
        }

        if (!empty($validation['errors']) && empty($validation['valid_rows'])) {
            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan format data pada file import:',
                'errors' => $validation['errors'],
            ], 422);
        }

        $job = $this->importService->createJob($validation['valid_rows'], $validation['duplicates']);

        return response()->json([
            'success' => true,
            'job_id' => $job['job_id'],
            'has_duplicates' => !empty($validation['duplicates']),
            'duplicates' => $validation['duplicates'],
            'redirect_url' => route('admin.users.import-progress', ['jobId' => $job['job_id']]),
        ]);
    }


    /**
     * Render Dedicated Import Progress View
     */
    public function progress(string $jobId)
    {
        $job = $this->importService->getJob($jobId);

        if (!$job) {
            return redirect()->route('admin.users.index')->with('error', 'Job import tidak ditemukan atau telah kedaluwarsa.');
        }

        return view('admin.users.import_progress', compact('job'));
    }

    /**
     * Get Job Status JSON
     */
    public function status(string $jobId)
    {
        $job = $this->importService->getJob($jobId);

        if (!$job) {
            return response()->json(['error' => 'Job tidak ditemukan'], 404);
        }

        return response()->json($this->importService->formatJobResponse($job));
    }

    /**
     * Execute Batch Import Step
     */
    public function step(Request $request, string $jobId)
    {
        $batchSize = $request->input('batch_size', 5);
        $response = $this->importService->processBatch($jobId, (int) $batchSize);

        return response()->json($response);
    }

    /**
     * Handle Duplicate Resolution Choice (suffix, skip)
     */
    public function resolveDuplicates(Request $request, string $jobId)
    {
        $request->validate([
            'action' => 'required|in:suffix,skip,cancel',
        ]);

        $job = $this->importService->getJob($jobId);

        if (!$job) {
            return response()->json(['error' => 'Job tidak ditemukan'], 404);
        }

        if ($request->action === 'cancel') {
            $job['status'] = 'cancelled';
            $job['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'warning',
                'message' => 'Import dibatalkan oleh Superadmin karena masalah duplikasi username.'
            ];
        } else {
            $job['duplicate_resolution'] = $request->action;
            $job['status'] = 'processing';
            $actionText = $request->action === 'suffix' ? 'Ubah Username (Tambah Suffix)' : 'Lewati Baris Duplikat';
            $job['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'info',
                'message' => "Superadmin memilih resolusi duplikat: {$actionText}"
            ];
        }

        $this->importService->updateJob($jobId, $job);

        return response()->json($this->importService->formatJobResponse($job));
    }

    /**
     * Cancel Import Job
     */
    public function cancel(string $jobId)
    {
        $job = $this->importService->getJob($jobId);

        if ($job) {
            $job['status'] = 'cancelled';
            $job['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'warning',
                'message' => 'Proses import dibatalkan oleh Superadmin.'
            ];
            $this->importService->updateJob($jobId, $job);
        }

        return response()->json([
            'success' => true,
            'message' => 'Proses import berhasil dibatalkan.'
        ]);
    }
}
