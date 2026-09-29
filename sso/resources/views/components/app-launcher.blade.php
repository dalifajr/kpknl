<section class="card-m3 p-3 p-md-4 mb-4" aria-labelledby="launcherTitle">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h4 id="launcherTitle" class="fw-bold mb-1">Aplikasi Anda</h4>
            <p class="text-secondary mb-0">Pilih aplikasi untuk membukanya di tab baru.</p>
        </div>
        @if(auth()->user()->isMaintenance())
            <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-primary rounded-pill">Kelola aplikasi</a>
        @endif
    </div>
    <nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Pintasan akun">
        <a class="btn btn-tonal rounded-pill" href="{{ route('profile.show') }}">Profil Saya</a>
        <a class="btn btn-tonal rounded-pill" href="{{ route('login-sessions.index') }}">Sesi Login</a>
        <button type="button" class="btn btn-tonal rounded-pill" onclick="document.getElementById('modalSsoRoleHelp').showModal()" aria-haspopup="dialog">Panduan Portal</button>
    </nav>
    @if($apps->isNotEmpty())
        <div class="row g-2 align-items-end mb-3" id="appSearchControls" hidden>
            <div class="col-sm-8 col-lg-6">
                <label for="appSearch" class="form-label fw-semibold">Cari aplikasi</label>
                <input id="appSearch" type="search" class="form-control rounded-3" placeholder="Nama atau deskripsi aplikasi" autocomplete="off" aria-controls="appGrid">
            </div>
            <div class="col-auto"><button id="clearAppSearch" type="button" class="btn btn-outline-secondary rounded-pill">Reset pencarian</button></div>
        </div>
        <p id="appSearchCount" class="text-secondary small" role="status" aria-live="polite">{{ $apps->count() }} aplikasi tersedia untuk akun Anda.</p>
    @endif
    <div id="appGrid" class="row g-3">
        @forelse($apps->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE) as $app)
            @php($blocked = $app->is_maintenance && !auth()->user()->isSuperadmin() && !auth()->user()->isMaintenance())
            <div class="col-xl-3 col-lg-4 col-md-6 col-12" data-app-card data-search="{{ $app->name . ' ' . $app->description }}">
                <article class="app-launch-card p-3 rounded-4 h-100 d-flex flex-column gap-3">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px">
                            @if($app->icon_url)
                                <img src="{{ $app->icon_url }}" alt="" width="56" height="56" style="object-fit:contain;padding:4px" loading="lazy">
                            @else
                                <i class="fa-solid fa-cube fs-3" aria-hidden="true"></i>
                            @endif
                        </div>
                        <div><span class="badge {{ $app->status_badge_class }} rounded-pill">{{ $app->status_label }}</span><h5 class="fs-6 fw-bold mt-2 mb-0">{{ $app->name }}</h5></div>
                    </div>
                    <p class="small text-secondary mb-0">{{ $app->description ?: 'Aplikasi internal KPKNL Palembang.' }}</p>
                    @if($app->pivot?->role)
                        <p class="small mb-0">Peran aplikasi: <strong>{{ $app->pivot->role }}</strong></p>
                    @endif
                    @if($blocked)
                        <p class="small text-secondary mt-auto mb-0">Sedang dalam pemeliharaan. Coba kembali setelah pengelola menyelesaikan pemeliharaan.</p>
                    @else
                        <a href="{{ route('oauth.authorize', ['client_id' => $app->client_id]) }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary rounded-pill mt-auto" aria-label="Buka {{ $app->name }} di tab baru">Buka aplikasi <i class="fa-solid fa-arrow-up-right-from-square ms-1" aria-hidden="true"></i></a>
                    @endif
                </article>
            </div>
        @empty
            <div class="col-12 py-4"><h5>Belum ada akses aplikasi</h5><p class="text-secondary mb-0">Hubungi pengelola akses dengan menyebutkan nama aplikasi dan tugas Anda.</p></div>
        @endforelse
    </div>
    <p id="appSearchEmpty" class="text-secondary py-4 mb-0" hidden>Tidak ada aplikasi yang cocok. Coba kata kunci lain atau reset pencarian.</p>
</section>
<style>
    [data-app-card][hidden], #appSearchControls[hidden] { display: none !important; }
    [data-app-card] article { overflow-wrap: anywhere; }
    [data-app-card] a:focus-visible { outline: 3px solid var(--bs-primary, #2563eb); outline-offset: 3px; }
</style>
<script src="{{ asset('js/app-launcher.js') }}" defer></script>
