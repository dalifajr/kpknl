// JavaScript, webhook.test.cjs; Node.js 22; Apps Script contract without Google requests.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../GOOGLE_APPS_SCRIPT_WEBHOOK.js'), 'utf8');
const status_header = 'Status Pendidikan dan\nPencantuman Gelar Akademik';
const canonical = 'Sudah Clear (sesuai dengan HRIS)';

function fixture(options = {}) {
    const headers = ['NAMA', 'NIP', 'JABATAN', status_header];
    const values = [[], [], [], [], headers, ['Nama Lama', '198501012010121001', 'Pelaksana', 'Belum Clear']];
    const writes = [];
    let released = false;
    const rule = {
        getCriteriaType: () => options.range ? 'range' : 'list',
        getCriteriaValues: () => options.range
            ? [{ getValues: () => [[canonical], ['Belum Clear']] }]
            : [[canonical, 'Belum Clear']],
    };
    const sheet = {
        getSheetId: () => 1668021245,
        getLastRow: () => values.length,
        getLastColumn: () => headers.length,
        getRange: (r, c, rows = 1, cols = 1) => ({
            getValues: () => Array.from({ length: rows }, (_, i) => Array.from({ length: cols }, (_, j) => values[r - 1 + i]?.[c - 1 + j] ?? '')),
            getDataValidation: () => c === 4 ? rule : null,
            setValue: value => { values[r - 1][c - 1] = value; writes.push({ r, c, value }); },
        }),
        insertRowAfter: r => values.splice(r, 0, []),
    };
    const ctx = vm.createContext({
        LockService: { getScriptLock: () => ({ tryLock: () => true, releaseLock: () => { released = true; } }) },
        SpreadsheetApp: { getActiveSpreadsheet: () => ({ getSheets: () => [sheet] }), flush() {}, DataValidationCriteria: { VALUE_IN_LIST: 'list', VALUE_IN_RANGE: 'range' } },
        ContentService: { MimeType: { JSON: 'json' }, createTextOutput: text => ({ setMimeType: () => JSON.parse(text) }) },
    });
    vm.runInContext(source, ctx);
    const post = data => ctx.doPost({ postData: { contents: JSON.stringify(data) } });
    return { post, writes, values, released: () => released };
}

for (const range of [false, true]) {
    test(`dropdown ${range ? 'range' : 'list'} writes the canonical option, preserving long NIP`, () => {
        const f = fixture({ range });
        const result = f.post({ nip: '198501012010121001', row_data: { NAMA: 'Nama Baru', 'STATUS PENDIDIKAN DAN PENCANTUMAN GELAR AKADEMIK': '  sudah clear  (sesuai dengan HRIS) ' } });
        assert.equal(result.status, 'success');
        assert.equal(f.values[5][3], canonical);
        assert.equal(f.values[5][1], "'198501012010121001");
        assert.ok(f.released());
    });
}
test('invalid dropdown rejects before writing any employee cell', () => {
    const f = fixture();
    const result = f.post({ nip: '198501012010121001', row_data: { NAMA: 'Nama Baru', 'STATUS PENDIDIKAN DAN PENCANTUMAN GELAR AKADEMIK': 'Tidak ada' } });
    assert.equal(result.status, 'error');
    assert.match(result.message, /dropdown/);
    assert.equal(f.writes.length, 0);
    assert.ok(f.released());
});
test('missing header reports an error instead of silently skipping', () => {
    const f = fixture();
    const result = f.post({ nip: '198501012010121001', row_data: { NAMA: 'Nama Baru', FAKULTAS: 'Ekonomi' } });
    assert.equal(result.status, 'error');
    assert.match(result.message, /FAKULTAS/);
    assert.equal(f.writes.length, 0);
});
test('NIP change finds original employee row', () => {
    const f = fixture();
    const result = f.post({ original_nip: '198501012010121001', nip: '198501012010121002', row_data: { NAMA: 'Nama Baru' } });
    assert.equal(result.status, 'success');
    assert.equal(f.values.length, 6);
    assert.equal(f.values[5][1], "'198501012010121002");
});
test('missing NIP refuses writes and releases the lock', () => {
    const f = fixture();
    assert.equal(f.post({ action: 'create', row_data: { NAMA: 'Nama Baru' } }).status, 'error');
    assert.equal(f.writes.length, 0);
    assert.ok(f.released());
});
test('retry after NIP change updates the same row', () => {
    const f = fixture();
    const payload = { original_nip: '198501012010121001', nip: '198501012010121002', row_data: { NAMA: 'Nama Baru' } };
    assert.equal(f.post(payload).status, 'success');
    assert.equal(f.post(payload).status, 'success');
    assert.equal(f.values.length, 6);
});
test('duplicate replacement NIP is rejected before writes', () => {
    const f = fixture();
    f.values.push(['Pegawai Lain', '198501012010121002', 'Pelaksana', 'Belum Clear']);
    assert.equal(f.post({ original_nip: '198501012010121001', nip: '198501012010121002', row_data: { NAMA: 'Nama Baru' } }).status, 'error');
    assert.equal(f.writes.length, 0);
});
