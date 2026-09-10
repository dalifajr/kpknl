<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetLease;
use Illuminate\Support\Facades\Storage;
use App\Exports\AssetsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = $this->buildFilteredQuery($request)
                        ->with(['province', 'regency', 'district', 'village'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(10)
                        ->withQueryString();

        return view('assets.index', compact('assets'));
    }

    public function create()
    {
        return view('assets.form');
    }

    public function store(Request $request)
    {
        $this->mergeRtRw($request);
        $validated = $this->validateAsset($request);

        // Auto generate Google Maps Link if lat & lng present
        if (!empty($validated['koordinat_latitude']) && !empty($validated['koordinat_longitude'])) {
            $validated['koordinat_link'] = "https://maps.google.com/?q={$validated['koordinat_latitude']},{$validated['koordinat_longitude']}";
        }

        if ($request->hasFile('foto')) {
            $fotoPaths = [];
            $files = is_array($request->file('foto')) ? $request->file('foto') : [$request->file('foto')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $filename = $file->hashName();
                    Storage::disk('public')->put('assets/foto/' . $filename, fopen($file->getPathname(), 'r'));
                    $fotoPaths[] = 'assets/foto/' . $filename;
                }
            }
            if (!empty($fotoPaths)) {
                $validated['foto_path'] = json_encode($fotoPaths);
            }
        }

        if ($request->hasFile('dokumen')) {
            $dokumenPaths = [];
            $files = is_array($request->file('dokumen')) ? $request->file('dokumen') : [$request->file('dokumen')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $filename = $file->hashName();
                    Storage::disk('public')->put('assets/dokumen/' . $filename, fopen($file->getPathname(), 'r'));
                    $dokumenPaths[] = 'assets/dokumen/' . $filename;
                }
            }
            if (!empty($dokumenPaths)) {
                $validated['dokumen_path'] = json_encode($dokumenPaths);
            }
        }

        unset($validated['foto'], $validated['dokumen']);
        $validated['papan_nama'] = $request->has('papan_nama');

        $asset = Asset::create($validated);
        $this->syncLeaseRecord($asset);

        return redirect()->route('assets.index')->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show($id)
    {
        $asset = Asset::withTrashed()->with([
            'province', 'regency', 'district', 'village',
            'oldProvince', 'oldRegency', 'oldDistrict', 'oldVillage',
            'leases'
        ])->findOrFail($id);

        return view('assets.show', compact('asset'));
    }

    public function restore($id)
    {
        if (!in_array(auth()->user()->role, ['superadmin', 'admin'])) {
            abort(403, 'Akses tidak diizinkan. Hanya Admin dan Superadmin yang dapat memulihkan aset.');
        }

        $asset = Asset::withTrashed()->findOrFail($id);
        $asset->restore();

        activity()
            ->performedOn($asset)
            ->causedBy(auth()->user())
            ->log('memulihkan aset (restore)');

        return redirect()->route('assets.show', $asset->id)->with('success', "Aset {$asset->kode_aset} berhasil dipulihkan.");
    }

    public function perpanjangSewa(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        $validated = $request->validate([
            'nama_penyewa' => 'required|string|max:255',
            'no_surat' => 'nullable|string|max:255',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'nilai_sewa' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Record new lease history
        AssetLease::create([
            'asset_id' => $asset->id,
            'nama_penyewa' => $validated['nama_penyewa'],
            'no_surat' => $validated['no_surat'],
            'tgl_mulai' => $validated['tgl_mulai'],
            'tgl_selesai' => $validated['tgl_selesai'],
            'nilai_sewa' => $validated['nilai_sewa'],
            'keterangan' => $validated['keterangan'] ?? 'Perpanjangan Kontrak Sewa',
            'user_id' => auth()->id(),
        ]);

        // Update active lease info on asset
        $asset->update([
            'kondisi_aset' => 'DISEWAKAN',
            'sewa_lelang_nama_pihak' => $validated['nama_penyewa'],
            'sewa_lelang_no_surat' => $validated['no_surat'],
            'sewa_lelang_tgl_mulai' => $validated['tgl_mulai'],
            'sewa_lelang_tgl_selesai' => $validated['tgl_selesai'],
            'sewa_lelang_nilai' => $validated['nilai_sewa'],
            'tanggal_update_kondisi' => now(),
        ]);

        activity()
            ->performedOn($asset)
            ->causedBy(auth()->user())
            ->log("memperpanjang kontrak sewa dengan {$validated['nama_penyewa']} senilai Rp " . number_format($validated['nilai_sewa'], 2, ',', '.'));

        return redirect()->route('assets.show', $asset->id)->with('success', "Kontrak sewa untuk aset {$asset->kode_aset} berhasil diperpanjang.");
    }

    public function edit(Asset $asset)
    {
        return view('assets.form', compact('asset'));
    }

    public function update(Request $request, Asset $asset)
    {
        $this->mergeRtRw($request);
        $validated = $this->validateAsset($request, $asset->id);

        if (!empty($validated['koordinat_latitude']) && !empty($validated['koordinat_longitude'])) {
            $validated['koordinat_link'] = "https://maps.google.com/?q={$validated['koordinat_latitude']},{$validated['koordinat_longitude']}";
        }

        // Process existing photo deletions if requested
        $fotoPaths = $asset->foto_paths ?? [];
        if ($request->has('delete_foto') && is_array($request->delete_foto)) {
            $fotoPaths = array_values(array_filter($fotoPaths, function($path) use ($request) {
                if (in_array($path, $request->delete_foto)) {
                    Storage::disk('public')->delete($path);
                    return false;
                }
                return true;
            }));
        }

        if ($request->hasFile('foto')) {
            $files = is_array($request->file('foto')) ? $request->file('foto') : [$request->file('foto')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $filename = $file->hashName();
                    Storage::disk('public')->put('assets/foto/' . $filename, fopen($file->getPathname(), 'r'));
                    $fotoPaths[] = 'assets/foto/' . $filename;
                }
            }
        }
        $validated['foto_path'] = !empty($fotoPaths) ? json_encode($fotoPaths) : null;

        // Process existing document deletions if requested
        $dokumenPaths = $asset->dokumen_paths ?? [];
        if ($request->has('delete_dokumen') && is_array($request->delete_dokumen)) {
            $dokumenPaths = array_values(array_filter($dokumenPaths, function($path) use ($request) {
                if (in_array($path, $request->delete_dokumen)) {
                    Storage::disk('public')->delete($path);
                    return false;
                }
                return true;
            }));
        }

        if ($request->hasFile('dokumen')) {
            $files = is_array($request->file('dokumen')) ? $request->file('dokumen') : [$request->file('dokumen')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $filename = $file->hashName();
                    Storage::disk('public')->put('assets/dokumen/' . $filename, fopen($file->getPathname(), 'r'));
                    $dokumenPaths[] = 'assets/dokumen/' . $filename;
                }
            }
        }
        $validated['dokumen_path'] = !empty($dokumenPaths) ? json_encode($dokumenPaths) : null;

        unset($validated['foto'], $validated['dokumen'], $validated['delete_foto'], $validated['delete_dokumen']);
        $validated['papan_nama'] = $request->has('papan_nama');

        $asset->update($validated);
        $this->syncLeaseRecord($asset);

        return redirect()->route('assets.show', $asset->id)->with('success', 'Aset berhasil diubah.');
    }

    public function uploadAttachment(Request $request, Asset $asset)
    {
        $request->validate([
            'foto.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:10240',
            'dokumen.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip|max:25600',
        ], [
            'foto.*.mimes' => 'Format foto harus berupa JPG, JPEG, PNG, WEBP, atau GIF.',
            'foto.*.max' => 'Ukuran maksimal tiap foto adalah 10 MB.',
            'dokumen.*.mimes' => 'Format dokumen harus berupa PDF, Word, Excel, Gambar, atau ZIP.',
            'dokumen.*.max' => 'Ukuran maksimal tiap dokumen adalah 25 MB.',
        ]);

        $newFotoCount = 0;
        $newDocCount = 0;

        $fotoPaths = $asset->foto_paths ?? [];
        if ($request->hasFile('foto')) {
            $files = is_array($request->file('foto')) ? $request->file('foto') : [$request->file('foto')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $filename = $file->hashName();
                    Storage::disk('public')->put('assets/foto/' . $filename, fopen($file->getPathname(), 'r'));
                    $fotoPaths[] = 'assets/foto/' . $filename;
                    $newFotoCount++;
                }
            }
            $asset->foto_path = !empty($fotoPaths) ? json_encode($fotoPaths) : null;
        }

        $dokumenPaths = $asset->dokumen_paths ?? [];
        if ($request->hasFile('dokumen')) {
            $files = is_array($request->file('dokumen')) ? $request->file('dokumen') : [$request->file('dokumen')];
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $filename = $file->hashName();
                    Storage::disk('public')->put('assets/dokumen/' . $filename, fopen($file->getPathname(), 'r'));
                    $dokumenPaths[] = 'assets/dokumen/' . $filename;
                    $newDocCount++;
                }
            }
            $asset->dokumen_path = !empty($dokumenPaths) ? json_encode($dokumenPaths) : null;
        }

        if ($newFotoCount > 0 || $newDocCount > 0) {
            $asset->save();
            
            $msg = [];
            if ($newFotoCount > 0) $msg[] = "{$newFotoCount} Foto";
            if ($newDocCount > 0) $msg[] = "{$newDocCount} Dokumen";
            $msgStr = implode(' dan ', $msg) . ' berhasil ditambahkan ke aset ' . $asset->kode_aset . '.';

            activity('assets')
                ->performedOn($asset)
                ->causedBy(auth()->user())
                ->log("Menambahkan lampiran ({$msgStr})");

            return redirect()->route('assets.show', $asset->id)->with('success', $msgStr);
        }

        return redirect()->route('assets.show', $asset->id)->with('error', 'Tidak ada berkas yang dipilih untuk diunggah.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        Asset::whereIn('id', $request->ids)->delete();
        return redirect()->route('assets.index')->with('success', count($request->ids) . ' Aset berhasil dihapus.');
    }

    // Helper for filtered query (respects filters and selected checkbox IDs)
    private function buildFilteredQuery(Request $request)
    {
        $query = Asset::query();

        if ($request->filled('ids')) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            return $query->whereIn('id', $ids);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_aset', 'like', "%{$search}%")
                  ->orWhere('alamat_namajalan', 'like', "%{$search}%")
                  ->orWhere('alamatlama_namajalan', 'like', "%{$search}%")
                  ->orWhere('wakil_kerja', 'like', "%{$search}%")
                  ->orWhere('jenis_aset', 'like', "%{$search}%")
                  ->orWhere('bank_asal', 'like', "%{$search}%")
                  ->orWhere('kondisi_aset', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis_aset')) {
            $query->where('jenis_aset', $request->jenis_aset);
        }

        if ($request->filled('kondisi_aset')) {
            $query->where('kondisi_aset', $request->kondisi_aset);
        }

        if (!$request->filled('print_all_data')) {
            if ($request->filled('tahun')) {
                $query->where('tahun', $request->tahun);
            }

            if ($request->filled('bulan')) {
                $query->whereMonth('tanggal_update_kondisi', $request->bulan);
            }

            if ($request->filled('semester')) {
                $query->where('semester', $request->semester);
            }
        }

        return $query;
    }

    // Export Laporan Semester (Excel & PDF)
    public function exportExcel(Request $request)
    {
        return (new AssetsExport($request))->download('Laporan_Semester_Aset.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = $this->buildFilteredQuery($request)->with(['province', 'regency', 'district', 'village', 'oldProvince', 'oldRegency', 'oldDistrict', 'oldVillage']);
        $assets = $query->orderBy('created_at', 'desc')->get();
        
        $semesterRoman = ($request->semester == '2') ? 'II' : 'I';
        $tahun = $request->tahun ?? ($assets->first()->tahun ?? date('Y'));

        $pdf = Pdf::loadView('assets.pdf', compact('assets', 'semesterRoman', 'tahun'))->setPaper('legal', 'landscape');
        return $pdf->stream('Laporan_Rekapitulasi_Pemeliharaan_Aset.pdf');
    }

    // Export Buku Profil (PDF)
    public function exportBukuProfilPdf(Request $request, $id = null)
    {
        if ($id) {
            $assets = Asset::where('id', $id)->with(['province', 'regency', 'district', 'village'])->get();
        } else {
            $query = $this->buildFilteredQuery($request)->with(['province', 'regency', 'district', 'village']);
            $assets = $query->get();
        }

        $pdf = Pdf::setOptions(['isRemoteEnabled' => true])
            ->loadView('assets.buku_profil_pdf', compact('assets'))
            ->setPaper('legal', 'portrait');
        return $pdf->stream('Laporan_Buku_Profil_Aset.pdf');
    }

    // Export Resume Profil Aset (PDF)
    public function exportResumePdf(Request $request)
    {
        $query = $this->buildFilteredQuery($request)
                      ->with(['regency', 'district', 'village']);
        $assets = $query->orderBy('created_at')->get();

        // Kelompokkan per kota/kabupaten, urutkan nama kota secara alfabetis
        $grouped = $assets->groupBy(function ($asset) {
            return $asset->regency->name ?? 'Tidak Diketahui';
        })->sortKeys();

        $pdf = Pdf::loadView('assets.resume_profil_pdf', compact('grouped'))
                  ->setPaper('a4', 'portrait');
        return $pdf->stream('Resume_Profil_Aset.pdf');
    }

    private function mergeRtRw(Request $request)
    {
        if ($request->has('alamat_rt') || $request->has('alamat_rw')) {
            $request->merge(['alamat_rt_rw' => ltrim(rtrim(trim($request->alamat_rt) . '/' . trim($request->alamat_rw), '/'), '/')]);
        }
        if ($request->has('alamatlama_rt') || $request->has('alamatlama_rw')) {
            $request->merge(['alamatlama_rt_rw' => ltrim(rtrim(trim($request->alamatlama_rt) . '/' . trim($request->alamatlama_rw), '/'), '/')]);
        }
    }

    private function validateAsset(Request $request, $id = null)
    {
        return $request->validate([
            'kode_aset' => 'required|unique:assets,kode_aset,' . $id,
            'jenis_aset' => 'required|in:Tanah,Bangunan,Tanah dan Bangunan',
            'bank_asal' => 'nullable|string',
            'permasalahan' => 'nullable|string',
            'alamatlama_namajalan' => 'nullable|string',
            'alamatlama_rt_rw' => 'nullable|string',
            'alamatlama_provinsi_id' => 'nullable|string',
            'alamatlama_kota_kab_id' => 'nullable|string',
            'alamatlama_kecamatan_id' => 'nullable|string',
            'alamatlama_kelurahan_id' => 'nullable|string',
            'alamatlama_kodepos' => 'nullable|string',
            'alamat_namajalan' => 'nullable|string',
            'alamat_rt_rw' => 'nullable|string',
            'alamat_provinsi_id' => 'nullable|string',
            'alamat_kota_kab_id' => 'nullable|string',
            'alamat_kecamatan_id' => 'nullable|string',
            'alamat_kelurahan_id' => 'nullable|string',
            'kodepos' => 'nullable|string',
            'batas_utara' => 'nullable|string',
            'batas_selatan' => 'nullable|string',
            'batas_timur' => 'nullable|string',
            'batas_barat' => 'nullable|string',
            'koordinat_latitude' => 'nullable|numeric',
            'koordinat_longitude' => 'nullable|numeric',
            'koordinat_link' => 'nullable|string',
            'jenis_bukti_kepemilikan' => 'nullable|string',
            'nomor_bukti_kepemilikan' => 'nullable|string',
            'luas_tanah' => 'nullable|numeric',
            'luas_bangunan' => 'nullable|numeric',
            'njop' => 'nullable|numeric',
            'kondisi_aset' => 'nullable|string',
            'kondisi_aset_lainlain' => 'nullable|string',
            'sewa_lelang_nama_pihak' => 'nullable|string',
            'sewa_lelang_tgl_mulai' => 'nullable|date',
            'sewa_lelang_tgl_selesai' => 'nullable|date',
            'sewa_lelang_nilai' => 'nullable|numeric',
            'sewa_lelang_no_surat' => 'nullable|string',
            'potensi_aset' => 'nullable|string',
            'papan_nama' => 'boolean',
            'wakil_kerja' => 'nullable|string',
            'keterangan_kondisi_aset' => 'nullable|string',
            'tanggal_update_kondisi' => 'nullable|date',
            'foto' => 'nullable',
            'foto.*' => 'nullable|image|max:2048',
            'dokumen' => 'nullable',
            'dokumen.*' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'semester' => 'nullable|string|in:1,2',
            'tahun' => 'nullable|integer',
        ]);
    }

    private function syncLeaseRecord(Asset $asset)
    {
        if (strtoupper($asset->kondisi_aset ?? '') === 'DISEWAKAN' && $asset->sewa_lelang_nama_pihak && $asset->sewa_lelang_tgl_mulai && $asset->sewa_lelang_tgl_selesai) {
            AssetLease::firstOrCreate(
                [
                    'asset_id' => $asset->id,
                    'nama_penyewa' => $asset->sewa_lelang_nama_pihak,
                    'tgl_mulai' => $asset->sewa_lelang_tgl_mulai,
                ],
                [
                    'no_surat' => $asset->sewa_lelang_no_surat,
                    'tgl_selesai' => $asset->sewa_lelang_tgl_selesai,
                    'nilai_sewa' => $asset->sewa_lelang_nilai ?? 0,
                    'keterangan' => 'Kontrak Sewa Aktif',
                    'user_id' => auth()->id(),
                ]
            );
        }
    }
}
