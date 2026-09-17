<?php

namespace App\Http\Controllers;

use App\Models\ChangeLog;
use App\Models\Jabatan;
use App\Models\PangkatGolongan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Services\GoogleSheetSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = Pegawai::with(['unitKerja', 'jabatan', 'pangkatGolongan'])
            ->where('is_active', true);

        // Search Keyword
        if ($request->filled('search')) {
            $keyword = trim($request->search);
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('nama_lengkap_gelar', 'like', "%{$keyword}%")
                  ->orWhere('nip', 'like', "%{$keyword}%")
                  ->orWhere('nik', 'like', "%{$keyword}%")
                  ->orWhere('nama_jabatan_raw', 'like', "%{$keyword}%")
                  ->orWhere('nama_universitas', 'like', "%{$keyword}%");
            });
        }

        // Filter Unit Kerja
        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        // Filter Tipe Pegawai (PNS / PPNPN)
        if ($request->filled('tipe_pegawai')) {
            $query->where('tipe_pegawai', $request->tipe_pegawai);
        }

        // Filter Pangkat Golongan
        if ($request->filled('pangkat_id')) {
            $query->where('pangkat_golongan_id', $request->pangkat_id);
        }

        // Filter Gender
        if ($request->filled('gender')) {
            $query->where('jenis_kelamin', $request->gender);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'no_urut');
        $sortOrder = $request->input('sort_order', 'asc');

        if (in_array($sortBy, ['no_urut', 'nama', 'nip', 'job_grade', 'usia_tahun', 'lama_palembang_tahun'])) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('no_urut', 'asc');
        }

        $perPage = $request->input('per_page', 10);
        $pegawais = $query->paginate($perPage)->withQueryString();

        $unitKerjas = UnitKerja::orderBy('urutan')->get();
        $pangkats = PangkatGolongan::where('golongan_ruang', '!=', '-')->orderByDesc('hirarki_level')->get();

        $totalCount = Pegawai::where('is_active', true)->count();
        $pnsCount = Pegawai::where('is_active', true)->where('tipe_pegawai', 'pns')->count();
        $ppnpnCount = Pegawai::where('is_active', true)->where('tipe_pegawai', 'ppnpn')->count();

        return view('pegawai.index', compact(
            'pegawais',
            'unitKerjas',
            'pangkats',
            'totalCount',
            'pnsCount',
            'ppnpnCount'
        ));
    }

    /**
     * Return employee profile detail for Modal popup
     */
    public function detail($id)
    {
        $pegawai = Pegawai::with(['unitKerja', 'jabatan', 'pangkatGolongan'])->findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return view('pegawai.detail_modal', compact('pegawai'));
        }

        return view('pegawai.show', compact('pegawai'));
    }

    /**
     * Modal Aggregate List Filter (For clickable cards & charts)
     */
    public function filterModal(Request $request)
    {
        $type = $request->input('type', 'all');
        $value = $request->input('value');
        $title = $request->input('title', 'Daftar Personil Pegawai');
        $context = 'default';

        $query = Pegawai::with(['unitKerja', 'jabatan', 'pangkatGolongan'])->where('is_active', true);

        switch ($type) {
            case 'total_pegawai':
                $title = 'Seluruh Personil KPKNL Palembang';
                break;

            case 'pns_definitif':
                $context = 'pns_definitif';
                $query->where('tipe_pegawai', 'pns');
                $title = 'Aparatur Sipil Negara (ASN) Definitif';
                break;

            case 'kgb_alert':
                $context = 'kgb';
                $title = 'Pegawai Jatuh Tempo Kenaikan Gaji Berkala (KGB)';
                // Fetch all active, and filter using accessor to ensure +2 years logic
                $all = $query->get();
                $pegawais = $all->filter(function ($p) {
                    $st = $p->kgb_status;
                    return in_array($st['status'], ['overdue', 'warning']);
                })->sortBy(function ($p) {
                    return $p->kgb_status['days_diff'];
                })->values();

                return view('pegawai.aggregate_list_modal', compact('pegawais', 'title', 'context'));

            case 'pensiun_radar':
                $context = 'pensiun';
                $query->where('usia_tahun', '>=', 53)->orderByDesc('usia_tahun');
                $title = 'Radar Pegawai Mendekati Batas Usia Pensiun (≥ 53 Tahun)';
                break;

            case 'tour_of_duty':
                $context = 'duty';
                $query->where('lama_palembang_tahun', '>=', 4)->orderByDesc('lama_palembang_tahun');
                $title = 'Pegawai Kesiapan Mutasi / Tour of Duty (> 4 Tahun di Palembang)';
                break;

            case 'unit_kerja':
                if ($value) {
                    $query->where(function ($q) use ($value) {
                        $q->where('unit_kerja_id', $value)
                          ->orWhereHas('unitKerja', function ($u) use ($value) {
                              $u->where('nama_unit', 'like', "%{$value}%")
                                ->orWhere('singkatan', 'like', "%{$value}%");
                          });
                    });
                }
                break;

            case 'generasi':
                $context = 'generasi';
                $now = Carbon::now();
                if ($value === 'gen_z') {
                    $title = 'Personil Generasi Z (< 28 Tahun)';
                    $query->where('usia_tahun', '<', 28);
                } elseif ($value === 'milenial') {
                    $title = 'Personil Generasi Milenial (28 - 43 Tahun)';
                    $query->whereBetween('usia_tahun', [28, 43]);
                } elseif ($value === 'gen_x') {
                    $title = 'Personil Generasi X (44 - 59 Tahun)';
                    $query->whereBetween('usia_tahun', [44, 59]);
                } elseif ($value === 'boomer') {
                    $title = 'Personil Baby Boomer (≥ 60 Tahun)';
                    $query->where('usia_tahun', '>=', 60);
                }
                break;

            case 'gender':
                if (in_array(strtoupper($value), ['L', 'LAKI-LAKI', 'PRIA'])) {
                    $query->where('jenis_kelamin', 'L');
                    $title = 'Daftar Personil Pegawai Laki-laki';
                } elseif (in_array(strtoupper($value), ['P', 'PEREMPUAN', 'WANITA'])) {
                    $query->where('jenis_kelamin', 'P');
                    $title = 'Daftar Personil Pegawai Perempuan';
                }
                break;

            case 'kategori_jabatan':
                if ($value === 'struktural') {
                    $title = 'Pejabat Struktural Definitif (Eselon III & IV)';
                    $query->where(function ($q) {
                        $q->where('nama_jabatan_raw', 'like', '%kepala%')
                          ->orWhere('nama_jabatan_raw', 'like', '%kasubbag%');
                    });
                } elseif ($value === 'fungsional') {
                    $title = 'Pejabat Fungsional Tertentu (Pelelang & Penilai)';
                    $query->where(function ($q) {
                        $q->where('nama_jabatan_raw', 'like', '%pelelang%')
                          ->orWhere('nama_jabatan_raw', 'like', '%penilai%')
                          ->orWhere('nama_jabatan_raw', 'like', '%ahli%');
                    });
                } elseif ($value === 'pelaksana') {
                    $title = 'Aparatur Staf Pelaksana';
                    $query->where(function ($q) {
                        $q->where('nama_jabatan_raw', 'not like', '%kepala%')
                          ->where('nama_jabatan_raw', 'not like', '%kasubbag%')
                          ->where('nama_jabatan_raw', 'not like', '%pelelang%')
                          ->where('nama_jabatan_raw', 'not like', '%penilai%');
                    });
                }
                break;

            case 'job_grade':
                if ($value) {
                    $grade = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                    $query->where('job_grade', $grade);
                    $title = "Daftar Pegawai Kelas Jabatan (Grade {$grade})";
                }
                break;

            case 'golongan':
                if ($value) {
                    $query->whereHas('pangkatGolongan', function ($q) use ($value) {
                        $q->where('golongan_ruang', $value);
                    })->orWhere('pangkat_golongan_raw', 'like', "%{$value}%");
                    $title = "Daftar Pegawai Pangkat & Golongan Ruang {$value}";
                }
                break;

            case 'pendidikan':
                $context = 'pendidikan';
                if ($value) {
                    $query->where('pendidikan_terakhir', 'like', "%{$value}%");
                    $title = "Pegawai Kualifikasi Pendidikan {$value}";
                }
                break;

            case 'kampus':
                $context = 'pendidikan';
                if ($value) {
                    $query->where('nama_universitas', 'like', "%{$value}%");
                    $title = "Almamater {$value}";
                }
                break;

            case 'masa_tugas':
                $context = 'duty';
                if ($value === 'gt_4') {
                    $query->where('lama_palembang_tahun', '>=', 4);
                    $title = 'Masa Penugasan Palembang: Lebih dari 4 Tahun';
                } elseif ($value === '2_4') {
                    $query->whereBetween('lama_palembang_tahun', [2, 3]);
                    $title = 'Masa Penugasan Palembang: 2 s.d. 4 Tahun';
                } else {
                    $query->where('lama_palembang_tahun', '<', 2);
                    $title = 'Masa Penugasan Palembang: Kurang dari 2 Tahun';
                }
                break;
        }

        $pegawais = $query->orderBy('no_urut')->get();

        return view('pegawai.aggregate_list_modal', compact('pegawais', 'title', 'context'));
    }

    /**
     * Get Pegawai Data for Edit Form JSON
     */
    public function getFormData($id = null)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['superadmin', 'maintenance', 'administrator'])) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $pegawai = $id ? Pegawai::with(['unitKerja', 'pangkatGolongan'])->findOrFail($id) : null;
        $unitKerjas = UnitKerja::orderBy('urutan')->get();
        $pangkats = PangkatGolongan::where('golongan_ruang', '!=', '-')->orderByDesc('hirarki_level')->get();

        return response()->json([
            'pegawai' => $pegawai,
            'unit_kerjas' => $unitKerjas,
            'pangkats' => $pangkats,
        ]);
    }

    /**
     * Store New Pegawai (Superadmin & Maintenance only) with Dual-Write
     */
    public function store(Request $request, GoogleSheetSyncService $syncService)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['superadmin', 'maintenance', 'administrator'])) {
            return response()->json(['success' => false, 'message' => 'Hanya Superadmin dan Maintenance yang berwenang menambah pegawai.'], 403);
        }

        $request->validate([
            'nama' => 'required|string|max:150',
            'nip' => 'nullable|string|max:30|unique:pegawai,nip',
            'nik' => 'nullable|string|max:30',
            'unit_kerja_id' => 'nullable|exists:unit_kerja,id',
            'pangkat_golongan_id' => 'nullable|exists:pangkat_golongan,id',
            'nama_jabatan_raw' => 'required|string|max:200',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $pegawai = new Pegawai();
            $pegawai->nama = trim($request->nama);
            $pegawai->nama_lengkap_gelar = trim($request->nama_lengkap_gelar ?: $request->nama);
            $pegawai->nip = trim($request->nip);
            $pegawai->nik = trim($request->nik);
            $pegawai->tipe_pegawai = $request->input('tipe_pegawai', 'pns');
            $pegawai->unit_kerja_id = $request->unit_kerja_id;
            $pegawai->pangkat_golongan_id = $request->pangkat_golongan_id;
            $pegawai->nama_jabatan_raw = trim($request->nama_jabatan_raw);
            $pegawai->job_grade = $request->filled('job_grade') ? (int) $request->job_grade : null;
            $pegawai->jenis_kelamin = $request->input('jenis_kelamin', 'L');
            $pegawai->tempat_lahir = trim($request->tempat_lahir);
            $pegawai->status_gelar = trim($request->status_gelar ?: 'Sudah Clear (sesuai dengan HRIS)');

            // Tanggal Lahir & Usia
            if ($request->filled('tanggal_lahir')) {
                $tglLahir = Carbon::parse($request->tanggal_lahir);
                $now = Carbon::now();
                $pegawai->tanggal_lahir = $tglLahir;
                $pegawai->usia_tahun = (int) $tglLahir->diffInYears($now);
                $pegawai->usia_bulan = (int) ($tglLahir->diffInMonths($now) % 12);
            }

            // TMT Palembang & Masa Palembang
            if ($request->filled('tmt_palembang')) {
                $tmtPlg = Carbon::parse($request->tmt_palembang);
                $now = Carbon::now();
                $pegawai->tmt_palembang = $tmtPlg;
                $pegawai->lama_palembang_tahun = (int) $tmtPlg->diffInYears($now);
                $pegawai->lama_palembang_bulan = (int) ($tmtPlg->diffInMonths($now) % 12);
            }

            // TMT KGB & TMT Golongan
            if ($request->filled('tmt_kgb')) {
                $pegawai->tmt_kgb = Carbon::parse($request->tmt_kgb);
            }
            if ($request->filled('tmt_golongan')) {
                $pegawai->tmt_golongan = Carbon::parse($request->tmt_golongan);
            }

            // Pendidikan
            $pegawai->pendidikan_terakhir = trim($request->pendidikan_terakhir);
            $pegawai->jurusan = trim($request->jurusan);
            $pegawai->nama_universitas = trim($request->nama_universitas);
            $pegawai->tahun_lulus = $request->filled('tahun_lulus') ? (int) $request->tahun_lulus : null;

            // Handle Avatar Upload safely without getRealPath() issues on Windows/Laragon
            if ($request->hasFile('avatar')) {
                $avatarPath = $this->handleAvatarUpload($request->file('avatar'));
                if ($avatarPath) {
                    $pegawai->avatar_url = $avatarPath;
                }
            }

            // Hitung No Urut Terakhir
            $lastNoUrut = Pegawai::max('no_urut') ?: 0;
            $pegawai->no_urut = $lastNoUrut + 1;
            $pegawai->is_active = true;

            $pegawai->save();

            // Record ChangeLog
            $log = ChangeLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'pegawai_id' => $pegawai->id,
                'nama_pegawai' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'action' => 'create',
                'description' => "Penambahan personil pegawai baru: {$pegawai->nama} ({$pegawai->nama_jabatan_raw})",
                'payload_after' => $pegawai->toArray(),
                'sync_status' => 'pending',
            ]);

            DB::commit();

            // Try Dual-Write to Google Sheet
            $syncResult = $syncService->pushRowUpdate($log);

            return response()->json([
                'success' => true,
                'message' => "Pegawai {$pegawai->nama} berhasil ditambahkan! " . ($syncResult['success'] ? 'Tersinkron ke Google Sheet.' : 'Data tersimpan lokal.'),
                'pegawai' => $pegawai,
                'sync' => $syncResult,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pegawai: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Existing Pegawai (Superadmin & Maintenance only) with Dual-Write
     */
    public function update(Request $request, $id, GoogleSheetSyncService $syncService)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->role, ['superadmin', 'maintenance', 'administrator'])) {
            return response()->json(['success' => false, 'message' => 'Hanya Superadmin dan Maintenance yang berwenang mengubah data pegawai.'], 403);
        }

        $pegawai = Pegawai::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:150',
            'nip' => 'nullable|string|max:30|unique:pegawai,nip,' . $id,
            'nik' => 'nullable|string|max:30',
            'unit_kerja_id' => 'nullable|exists:unit_kerja,id',
            'pangkat_golongan_id' => 'nullable|exists:pangkat_golongan,id',
            'nama_jabatan_raw' => 'required|string|max:200',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $payloadBefore = $pegawai->toArray();

            $pegawai->nama = trim($request->nama);
            $pegawai->nama_lengkap_gelar = trim($request->nama_lengkap_gelar ?: $request->nama);
            $pegawai->nip = trim($request->nip);
            $pegawai->nik = trim($request->nik);
            $pegawai->tipe_pegawai = $request->input('tipe_pegawai', $pegawai->tipe_pegawai);
            $pegawai->unit_kerja_id = $request->unit_kerja_id;
            $pegawai->pangkat_golongan_id = $request->pangkat_golongan_id;
            $pegawai->nama_jabatan_raw = trim($request->nama_jabatan_raw);
            $pegawai->job_grade = $request->filled('job_grade') ? (int) $request->job_grade : $pegawai->job_grade;
            $pegawai->jenis_kelamin = $request->input('jenis_kelamin', $pegawai->jenis_kelamin);
            $pegawai->tempat_lahir = trim($request->tempat_lahir);
            $pegawai->status_gelar = trim($request->status_gelar ?: $pegawai->status_gelar);

            // Tanggal Lahir & Usia
            if ($request->filled('tanggal_lahir')) {
                $tglLahir = Carbon::parse($request->tanggal_lahir);
                $now = Carbon::now();
                $pegawai->tanggal_lahir = $tglLahir;
                $pegawai->usia_tahun = (int) $tglLahir->diffInYears($now);
                $pegawai->usia_bulan = (int) ($tglLahir->diffInMonths($now) % 12);
            }

            // TMT Palembang & Masa Palembang
            if ($request->filled('tmt_palembang')) {
                $tmtPlg = Carbon::parse($request->tmt_palembang);
                $now = Carbon::now();
                $pegawai->tmt_palembang = $tmtPlg;
                $pegawai->lama_palembang_tahun = (int) $tmtPlg->diffInYears($now);
                $pegawai->lama_palembang_bulan = (int) ($tmtPlg->diffInMonths($now) % 12);
            }

            // TMT KGB & TMT Golongan
            if ($request->filled('tmt_kgb')) {
                $pegawai->tmt_kgb = Carbon::parse($request->tmt_kgb);
            }
            if ($request->filled('tmt_golongan')) {
                $pegawai->tmt_golongan = Carbon::parse($request->tmt_golongan);
            }

            // Pendidikan
            $pegawai->pendidikan_terakhir = trim($request->pendidikan_terakhir);
            $pegawai->jurusan = trim($request->jurusan);
            $pegawai->nama_universitas = trim($request->nama_universitas);
            $pegawai->tahun_lulus = $request->filled('tahun_lulus') ? (int) $request->tahun_lulus : $pegawai->tahun_lulus;

            // Handle Avatar Upload safely without getRealPath() issues on Windows/Laragon
            if ($request->hasFile('avatar')) {
                $avatarPath = $this->handleAvatarUpload($request->file('avatar'), $pegawai->id);
                if ($avatarPath) {
                    if (!empty($pegawai->avatar_url) && Storage::disk('public')->exists($pegawai->avatar_url)) {
                        Storage::disk('public')->delete($pegawai->avatar_url);
                    }
                    $pegawai->avatar_url = $avatarPath;
                }
            }

            $pegawai->save();

            $payloadAfter = $pegawai->toArray();

            // Track specific field changes
            $changes = [];
            foreach ($payloadAfter as $k => $v) {
                if (($payloadBefore[$k] ?? null) != $v && !in_array($k, ['updated_at', 'created_at'])) {
                    $changes[$k] = [
                        'before' => $payloadBefore[$k] ?? null,
                        'after' => $v,
                    ];
                }
            }

            // Record ChangeLog
            $log = ChangeLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'pegawai_id' => $pegawai->id,
                'nama_pegawai' => $pegawai->nama,
                'nip' => $pegawai->nip,
                'action' => 'update',
                'description' => "Pembaruan data pegawai: {$pegawai->nama} (" . count($changes) . " kolom diubah)",
                'changes' => $changes,
                'payload_before' => $payloadBefore,
                'payload_after' => $payloadAfter,
                'sync_status' => 'pending',
            ]);

            DB::commit();

            // Try Dual-Write to Google Sheet
            $syncResult = $syncService->pushRowUpdate($log);

            return response()->json([
                'success' => true,
                'message' => "Data pegawai {$pegawai->nama} berhasil diperbarui! " . ($syncResult['success'] ? 'Tersinkron ke Google Sheet.' : 'Data tersimpan lokal.'),
                'pegawai' => $pegawai,
                'sync' => $syncResult,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui pegawai: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Safely store uploaded avatar file preventing Windows/Laragon getRealPath empty string bug.
     */
    private function handleAvatarUpload($file, ?int $pegawaiId = null): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $extension = strtolower($file->extension() ?: ($file->guessExtension() ?: 'jpg'));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }
        $filename = 'avatar_' . ($pegawaiId ?: uniqid()) . '_' . time() . '.' . $extension;

        // Direct binary content write prevents FilesystemAdapter::putFileAs fopen('', 'r') on Windows
        $pathname = $file->getPathname();
        if ($pathname && file_exists($pathname)) {
            $contents = @file_get_contents($pathname);
            if ($contents !== false) {
                Storage::disk('public')->put('avatars/' . $filename, $contents);
                return 'avatars/' . $filename;
            }
        }

        // Fallback to native move
        $targetDir = Storage::disk('public')->path('avatars');
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        $file->move($targetDir, $filename);
        return 'avatars/' . $filename;
    }
}
