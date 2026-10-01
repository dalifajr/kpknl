<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use ZipArchive;

class UserExportService
{
    /**
     * Build base query with relations and role visibility constraints.
     */
    public function getFilteredUsersQuery(array $filters = []): Builder
    {
        $query = User::with(['roles', 'applications']);

        // Superadmin tidak boleh mengekspor atau melihat akun dengan role maintenance
        if (auth()->check() && auth()->user()->isSuperadmin()) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'maintenance');
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $role = $filters['role'];
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name');
    }

    /**
     * Map users collection to structured array for export.
     */
    public function formatUsersData($users): array
    {
        $rows = [];
        foreach ($users as $index => $user) {
            $globalRole = $user->roles->first()?->display_name ?? ($user->roles->first()?->name ?? 'User');

            $appDetails = [];
            foreach ($user->applications as $app) {
                $appRole = !empty($app->pivot->role) ? ucfirst($app->pivot->role) : 'Bawaan Akun';
                $appDetails[] = "{$app->name} ({$appRole})";
            }
            $assignedAppsText = count($appDetails) > 0 ? implode('; ', $appDetails) : 'Belum Ada Aplikasi';

            $rows[] = [
                'no' => $index + 1,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $globalRole,
                'status' => ucfirst($user->status),
                'app_count' => $user->applications->count(),
                'assigned_apps' => $assignedAppsText,
                'created_at' => $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-',
            ];
        }

        return $rows;
    }

    /**
     * Generate CSV string with UTF-8 BOM.
     */
    public function generateCsv(array $data): string
    {
        $output = fopen('php://memory', 'w');

        // Write UTF-8 BOM so Microsoft Excel opens special characters correctly
        fputs($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, [
            'No',
            'Username',
            'Nama Lengkap',
            'Email',
            'Role Global SSO',
            'Status Akun',
            'Jumlah Aplikasi Di-assign',
            'Aplikasi & Role Penugasan',
            'Tanggal Terdaftar',
        ]);

        foreach ($data as $row) {
            fputcsv($output, [
                $row['no'],
                $row['username'],
                $row['name'],
                $row['email'],
                $row['role'],
                $row['status'],
                $row['app_count'],
                $row['assigned_apps'],
                $row['created_at'],
            ]);
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        return $content;
    }

    /**
     * Generate OpenXML (.xlsx) binary.
     */
    public function generateXlsx(array $data): string
    {
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
  <fonts count="2">
    <font><sz val="10"/><color theme="1"/><name val="Calibri"/><family val="2"/></font>
    <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0B3B60"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFD1D5DB"/></left>
      <right style="thin"><color rgb="FFD1D5DB"/></right>
      <top style="thin"><color rgb="FFD1D5DB"/></top>
      <bottom style="thin"><color rgb="FFD1D5DB"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="3">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>
  </cellXfs>
</styleSheet>';

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Daftar User SSO" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';

        $rowCount = count($data) + 1;
        $sheetData = "<row r=\"1\" ht=\"24\" customHeight=\"1\">\n";
        $headers = [
            'A1' => 'No',
            'B1' => 'Username',
            'C1' => 'Nama Lengkap',
            'D1' => 'Email',
            'E1' => 'Role Global SSO',
            'F1' => 'Status Akun',
            'G1' => 'Jumlah Aplikasi',
            'H1' => 'Aplikasi & Role Penugasan',
            'I1' => 'Tanggal Terdaftar',
        ];

        foreach ($headers as $cellRef => $title) {
            $sheetData .= "  <c r=\"{$cellRef}\" t=\"inlineStr\" s=\"1\"><is><t>" . htmlspecialchars($title, ENT_XML1, 'UTF-8') . "</t></is></c>\n";
        }
        $sheetData .= "</row>\n";

        foreach ($data as $idx => $row) {
            $rNum = $idx + 2;
            $sheetData .= "<row r=\"{$rNum}\">\n";
            $sheetData .= "  <c r=\"A{$rNum}\" s=\"2\"><v>{$row['no']}</v></c>\n";
            $sheetData .= "  <c r=\"B{$rNum}\" t=\"inlineStr\" s=\"2\"><is><t>" . htmlspecialchars((string)$row['username'], ENT_XML1, 'UTF-8') . "</t></is></c>\n";
            $sheetData .= "  <c r=\"C{$rNum}\" t=\"inlineStr\" s=\"2\"><is><t>" . htmlspecialchars((string)$row['name'], ENT_XML1, 'UTF-8') . "</t></is></c>\n";
            $sheetData .= "  <c r=\"D{$rNum}\" t=\"inlineStr\" s=\"2\"><is><t>" . htmlspecialchars((string)$row['email'], ENT_XML1, 'UTF-8') . "</t></is></c>\n";
            $sheetData .= "  <c r=\"E{$rNum}\" t=\"inlineStr\" s=\"2\"><is><t>" . htmlspecialchars((string)$row['role'], ENT_XML1, 'UTF-8') . "</t></is></c>\n";
            $sheetData .= "  <c r=\"F{$rNum}\" t=\"inlineStr\" s=\"2\"><is><t>" . htmlspecialchars((string)$row['status'], ENT_XML1, 'UTF-8') . "</t></is></c>\n";
            $sheetData .= "  <c r=\"G{$rNum}\" s=\"2\"><v>{$row['app_count']}</v></c>\n";
            $sheetData .= "  <c r=\"H{$rNum}\" t=\"inlineStr\" s=\"2\"><is><t>" . htmlspecialchars((string)$row['assigned_apps'], ENT_XML1, 'UTF-8') . "</t></is></c>\n";
            $sheetData .= "  <c r=\"I{$rNum}\" t=\"inlineStr\" s=\"2\"><is><t>" . htmlspecialchars((string)$row['created_at'], ENT_XML1, 'UTF-8') . "</t></is></c>\n";
            $sheetData .= "</row>\n";
        }

        $sheet1Xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <dimension ref="A1:I' . max(1, $rowCount) . '"/>
  <cols>
    <col min="1" max="1" width="6" customWidth="1"/>
    <col min="2" max="2" width="20" customWidth="1"/>
    <col min="3" max="3" width="28" customWidth="1"/>
    <col min="4" max="4" width="28" customWidth="1"/>
    <col min="5" max="5" width="18" customWidth="1"/>
    <col min="6" max="6" width="14" customWidth="1"/>
    <col min="7" max="7" width="16" customWidth="1"/>
    <col min="8" max="8" width="50" customWidth="1"/>
    <col min="9" max="9" width="20" customWidth="1"/>
  </cols>
  <sheetData>
' . $sheetData . '
  </sheetData>
</worksheet>';

        if (class_exists('ZipArchive')) {
            $tempFile = tempnam(sys_get_temp_dir(), 'export_xlsx_');
            $zip = new ZipArchive();
            if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFromString('[Content_Types].xml', $typesXml);
                $zip->addFromString('_rels/.rels', $relsXml);
                $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXml);
                $zip->addFromString('xl/workbook.xml', $workbookXml);
                $zip->addFromString('xl/styles.xml', $stylesXml);
                $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1Xml);
                $zip->close();
            }
            $binary = file_get_contents($tempFile);
            @unlink($tempFile);
            return $binary;
        }

        $writer = new SimpleZipWriter();
        $writer->addFile('[Content_Types].xml', $typesXml);
        $writer->addFile('_rels/.rels', $relsXml);
        $writer->addFile('xl/_rels/workbook.xml.rels', $workbookRelsXml);
        $writer->addFile('xl/workbook.xml', $workbookXml);
        $writer->addFile('xl/styles.xml', $stylesXml);
        $writer->addFile('xl/worksheets/sheet1.xml', $sheet1Xml);

        return $writer->getZipContent();
    }
}
