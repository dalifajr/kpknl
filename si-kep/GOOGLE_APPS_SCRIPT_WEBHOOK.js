/**
 * ==============================================================================
 * GOOGLE APPS SCRIPT (GAS) WEBHOOK - SI-KEP KPKNL PALEMBANG
 * ==============================================================================
 * Skrip ini dipasang pada Google Spreadsheet Kepegawaian KPKNL Palembang
 * untuk menerima pembaruan data secara real-time dari aplikasi web SI-KEP.
 *
 * CARA PEMASANGAN DI GOOGLE SPREADSHEET:
 * 1. Buka spreadsheet: https://docs.google.com/spreadsheets/d/1-pfEY_56CvIBaaCyxrfjv5nitEMHhPQCXKzuwdxX6hA
 * 2. Klik menu: Extensions (Ekstensi) > Apps Script
 * 3. Hapus semua kode default, lalu salin (copy-paste) seluruh isi skrip ini.
 * 4. Klik tombol "Save" (ikon disket / Ctrl + S).
 * 5. Klik tombol "Deploy" (di pojok kanan atas) > "New deployment" (Deployment baru).
 * 6. Pilih tipe: "Web app" (ikon roda gerigi di sebelah Select type).
 * 7. Konfigurasi Web app:
 *    - Description : Webhook SI-KEP KPKNL Palembang
 *    - Execute as  : Me (email akun Anda)
 *    - Who has access: Anyone (Siapa saja)  <--- SANGAT PENTING!
 * 8. Klik "Deploy" > Berikan izin otorisasi (Authorize access) > Advanced > Go to (unsafe).
 * 9. Salin "Web app URL" (akhiran /exec) dan tempelkan di Pengaturan Spreadsheet SI-KEP.
 * ==============================================================================
 */

function doPost(e) {
  var lock = LockService.getScriptLock();
  // Tunggu hingga 15 detik jika ada request bersamaan
  var hasLock = lock.tryLock(15000);
  if (!hasLock) {
    return respondJson({
      status: "error",
      message: "Server Google Spreadsheet sedang sibuk memproses permintaan lain. Coba beberapa saat lagi."
    });
  }

  try {
    if (!e || !e.postData || !e.postData.contents) {
      return respondJson({
        status: "error",
        message: "Tidak ada data payload POST yang diterima."
      });
    }

    var payload = JSON.parse(e.postData.contents);
    var action = payload.action || 'update';

    // 1. Tangani Ping / Check Permission Probe dari SI-KEP
    if (action === 'check_permission' || payload.ping === true) {
      return respondJson({
        status: "success",
        message: "Webhook Google Apps Script SI-KEP terhubung aktif dan siap menerima data."
      });
    }

    var ss = SpreadsheetApp.getActiveSpreadsheet();
    
    // 2. Temukan Sheet yang Tepat (Cari berdasarkan GID 1668021245 atau nama 'Daftar Pegawai' atau sheet berisi kolom NIP)
    var sheet = findTargetSheet(ss);
    if (!sheet) {
      throw new Error("Lembar kerja 'Daftar Pegawai' tidak ditemukan di spreadsheet ini.");
    }

    var lastRow = sheet.getLastRow();
    var lastCol = sheet.getLastColumn();
    if (lastRow < 5) {
      throw new Error("Struktur sheet kosong atau baris header tidak ditemukan.");
    }

    // 3. Deteksi Baris Header Kolom (Baris ke-5 pada spreadsheet KPKNL Palembang)
    // Mencari baris yang mengandung 'NIP' dan 'NAMA'
    var headerRowIndex = -1;
    var maxSearchRow = Math.min(lastRow, 10);
    for (var r = 1; r <= maxSearchRow; r++) {
      var rowValues = sheet.getRange(r, 1, 1, lastCol).getValues()[0];
      var joined = rowValues.join(';').toUpperCase();
      if (joined.indexOf('NAMA') !== -1 && joined.indexOf('NIP') !== -1 && joined.indexOf('JABATAN') !== -1) {
        headerRowIndex = r;
        break;
      }
    }

    if (headerRowIndex === -1) {
      throw new Error("Baris header kolom 'NAMA', 'NIP', dan 'JABATAN' tidak ditemukan di lembar kerja.");
    }

    // Buat pemetaan nama kolom ke nomor indeks kolom (1-based)
    var headerHeaders = sheet.getRange(headerRowIndex, 1, 1, lastCol).getValues()[0];
    var colMap = {};
    for (var c = 0; c < headerHeaders.length; c++) {
      var hName = String(headerHeaders[c]).trim().toUpperCase();
      if (hName) {
        colMap[hName] = c + 1;
      }
    }

    var targetNip = cleanNip(payload.nip || (payload.data ? payload.data.nip : ''));
    var data = payload.row_data || payload.data || {};
    var nipCol = colMap['NIP'] || 3;
    var dataStartRow = headerRowIndex + 1;

    // Jika ada baris penomoran (1, 2, 3...) setelah header, lewati baris tersebut
    var firstDataRowVals = sheet.getRange(dataStartRow, 1, 1, Math.min(lastCol, 10)).getValues()[0];
    if (isRowNumericNumbers(firstDataRowVals)) {
      dataStartRow++;
    }

    // 4. Cari Baris Pegawai Berdasarkan NIP
    var targetRow = -1;
    if (targetNip && lastRow >= dataStartRow) {
      var nipRange = sheet.getRange(dataStartRow, nipCol, (lastRow - dataStartRow + 1), 1).getValues();
      for (var i = 0; i < nipRange.length; i++) {
        var cellNip = cleanNip(nipRange[i][0]);
        if (cellNip === targetNip) {
          targetRow = dataStartRow + i;
          break;
        }
      }
    }

    // 5. Tangani Penambahan Data Baru (CREATE)
    if (action === 'create' || (action === 'update' && targetRow === -1 && targetNip)) {
      if (targetRow === -1) {
        // Cari baris akhir data PNS (sebelum banner motto / baris kosong sebelum PPNPN)
        targetRow = findPnsInsertRow(sheet, dataStartRow, lastRow, colMap);
        sheet.insertRowAfter(targetRow - 1);
      }
    }

    if (targetRow === -1) {
      return respondJson({
        status: "error",
        message: "Pegawai dengan NIP '" + targetNip + "' tidak ditemukan di Google Spreadsheet."
      });
    }

    // 6. Tulis Data ke Sel Kolom yang Sesuai
    function setCell(colName, val) {
      if (colMap[colName] && val !== undefined && val !== null && val !== '') {
        sheet.getRange(targetRow, colMap[colName]).setValue(val);
      }
    }

    // Tulis data pokok
    if (data.nama || data['NAMA']) setCell('NAMA', data.nama || data['NAMA']);
    if (data.nama_lengkap_gelar || data['NAMA DI DATA POKOK HRIS']) setCell('NAMA DI DATA POKOK HRIS', data.nama_lengkap_gelar || data['NAMA DI DATA POKOK HRIS']);
    if (targetNip) sheet.getRange(targetRow, nipCol).setValue("'" + targetNip);
    if (data.nik || data['NIK']) setCell('NIK', "'" + cleanNip(data.nik || data['NIK']));
    if (data.nama_jabatan_raw || data['JABATAN']) setCell('JABATAN', data.nama_jabatan_raw || data['JABATAN']);
    if (data.per_jabatan || data['PER.JABATAN']) setCell('PER.JABATAN', data.per_jabatan || data['PER.JABATAN']);
    if (data.pangkat_golongan_raw || data['PANGKAT / GOLONGAN']) setCell('PANGKAT / GOLONGAN', data.pangkat_golongan_raw || data['PANGKAT / GOLONGAN']);
    if (data.job_grade || data['GRADING']) setCell('GRADING', data.job_grade || data['GRADING']);
    if (data.tempat_lahir || data['TEMPAT LAHIR']) setCell('TEMPAT LAHIR', data.tempat_lahir || data['TEMPAT LAHIR']);
    if (data.tanggal_lahir || data['TGL LAHIR']) setCell('TGL LAHIR', formatDate(data.tanggal_lahir || data['TGL LAHIR']));
    if (data.tmt_nip || data['TMT NIP']) setCell('TMT NIP', formatDate(data.tmt_nip || data['TMT NIP']));
    if (data.tmt_eselon || data['TMT ESELON']) setCell('TMT ESELON', formatDate(data.tmt_eselon || data['TMT ESELON']));
    if (data.tmt_palembang || data['TMT PALEMBANG']) setCell('TMT PALEMBANG', formatDate(data.tmt_palembang || data['TMT PALEMBANG']));
    if (data.tmt_golongan || data['TMT GOLONGAN']) setCell('TMT GOLONGAN', formatDate(data.tmt_golongan || data['TMT GOLONGAN']));
    if (data.tmt_kgb || data['TMT KGB']) setCell('TMT KGB', formatDate(data.tmt_kgb || data['TMT KGB']));
    if (data.tmt_grading || data['TMT GRADING']) setCell('TMT GRADING', formatDate(data.tmt_grading || data['TMT GRADING']));
    if (data.jenis_kelamin || data['JENIS KELAMIN']) setCell('JENIS KELAMIN', (data.jenis_kelamin || data['JENIS KELAMIN']).toUpperCase());
    if (data.pendidikan_terakhir || data['PENDIDIKAN (HRIS)']) setCell('PENDIDIKAN (HRIS)', data.pendidikan_terakhir || data['PENDIDIKAN (HRIS)']);
    if (data.fakultas || data['FAKULTAS']) setCell('FAKULTAS', data.fakultas || data['FAKULTAS']);
    if (data.jurusan || data['JURUSAN']) setCell('JURUSAN', data.jurusan || data['JURUSAN']);
    if (data.tahun_lulus || data['TAHUN LULUS']) setCell('TAHUN LULUS', data.tahun_lulus || data['TAHUN LULUS']);
    if (data.nama_universitas || data['NAMA UNIVERSITAS']) setCell('NAMA UNIVERSITAS', data.nama_universitas || data['NAMA UNIVERSITAS']);
    if (data.tmt_ue_iv || data['TMT UE IV']) setCell('TMT UE IV', data.tmt_ue_iv || data['TMT UE IV']);
    if (data.lama_bertugas_ue_iv || data['LAMA BERTUGAS DI UE IV']) setCell('LAMA BERTUGAS DI UE IV', data.lama_bertugas_ue_iv || data['LAMA BERTUGAS DI UE IV']);
    if (data.status_gelar || data['Status Pendidikan dan Pencantuman Gelar Akademik']) setCell('Status Pendidikan dan Pencantuman Gelar Akademik', data.status_gelar || data['Status Pendidikan dan Pencantuman Gelar Akademik']);

    SpreadsheetApp.flush();

    return respondJson({
      status: "success",
      message: "Data pegawai " + (data.nama || targetNip) + " berhasil disinkronkan ke baris " + targetRow + " Google Spreadsheet.",
      row: targetRow,
      nip: targetNip,
      action: action
    });

  } catch (err) {
    return respondJson({
      status: "error",
      message: "Gagal memproses di Google Apps Script: " + err.toString()
    });
  } finally {
    lock.releaseLock();
  }
}

function doGet(e) {
  return respondJson({
    status: "success",
    message: "Webhook Google Apps Script SI-KEP KPKNL Palembang aktif dan siap menerima request POST."
  });
}

function findTargetSheet(ss) {
  var sheets = ss.getSheets();
  for (var i = 0; i < sheets.length; i++) {
    if (sheets[i].getSheetId() === 1668021245) return sheets[i];
    if (sheets[i].getName().toLowerCase().indexOf('pegawai') !== -1) return sheets[i];
  }
  return sheets[0];
}

function cleanNip(nip) {
  return String(nip || '').trim().replace(/['"`\s\.]/g, '');
}

function formatDate(val) {
  if (!val) return '';
  var s = String(val).trim();
  if (s.indexOf('T') !== -1) {
    s = s.split('T')[0];
  }
  var parts = s.split('-');
  if (parts.length === 3 && parts[0].length === 4) {
    // YYYY-MM-DD -> D/M/YYYY
    return parseInt(parts[2], 10) + '/' + parseInt(parts[1], 10) + '/' + parts[0];
  }
  return s;
}

function isRowNumericNumbers(vals) {
  var count = 0;
  for (var i = 0; i < vals.length; i++) {
    if (typeof vals[i] === 'number' || (String(vals[i]).trim() !== '' && !isNaN(vals[i]))) {
      count++;
    }
  }
  return count >= 3;
}

function findPnsInsertRow(sheet, dataStartRow, lastRow, colMap) {
  // Cari baris kosong atau baris bertuliskan 'KPKNL Palembang berkomitmen'
  var range = sheet.getRange(dataStartRow, 1, (lastRow - dataStartRow + 1), 3).getValues();
  for (var r = 0; r < range.length; r++) {
    var colA = String(range[r][0]).trim();
    var colB = String(range[r][1]).trim();
    if (colA === '' && colB.indexOf('berkomitmen') !== -1) {
      return dataStartRow + r;
    }
    if (colA === '' && colB === '') {
      return dataStartRow + r;
    }
  }
  return lastRow + 1;
}

function respondJson(obj) {
  return ContentService
    .createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}
