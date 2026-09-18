<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aplikasi Manajemen Aset Eks BPPN')</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Materialize CSS -->
    <link rel="stylesheet" href="{{ asset('css/materialize.min.css') }}">
    
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f5f5f5;
        }
        main {
            flex: 1 0 auto;
        }
        header, main, footer {
            padding-left: 300px;
        }
        @media only screen and (max-width : 992px) {
            header, main, footer {
                padding-left: 0;
            }
        }
        .brand-logo {
            padding-left: 15px !important;
        }
        .sidenav-trigger {
            display: none;
        }
        @media only screen and (max-width : 992px) {
            .sidenav-trigger {
                display: block;
            }
        }
        
        /* Mini Sidebar Styles */
        body.sidebar-mini header, body.sidebar-mini main, body.sidebar-mini footer {
            padding-left: 70px;
        }
        body.sidebar-mini #slide-out {
            width: 70px;
            overflow-x: hidden;
        }
        body.sidebar-mini #slide-out a {
            padding: 0;
            text-align: center;
        }
        body.sidebar-mini #slide-out a i.material-icons {
            margin: 0;
            font-size: 24px;
            line-height: 48px;
        }
        body.sidebar-mini #slide-out .user-view, 
        body.sidebar-mini #slide-out .clock-container,
        body.sidebar-mini #slide-out a span {
            display: none;
        }
        
        header {
            position: sticky;
            top: 0;
            z-index: 997;
        }

        #slide-out {
            display: flex;
            flex-direction: column;
        }
        /* Sidebar Footer Styles */
        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            text-align: center;
            background-color: #FFFFFF;
            border-top: 1px solid #e0e0e0;
        }
        .footer-time-container {
            margin-bottom: 16px;
            color: rgba(0, 0, 0, 0.54);
        }
        .sidebar-date {
            font-size: 13px;
            font-weight: 500;
        }
        .sidebar-time {
            font-size: 22px;
            font-weight: 700;
            color: #009688;
            font-family: monospace;
            letter-spacing: 1px;
            margin-top: 4px;
        }
        .sidebar-logo {
            width: 64px;
            height: auto;
            margin-bottom: 8px;
        }
        .footer-text {
            font-size: 12px;
            color: rgba(0, 0, 0, 0.54);
            line-height: 1.4;
        }
        .footer-text strong {
            color: rgba(0, 0, 0, 0.87);
        }

        /* Force all dropdown text (Selects, Action Dropdowns, Export Dropdowns) to black */
        .select-wrapper input.select-dropdown,
        .select-wrapper .caret,
        .dropdown-content li > a,
        .dropdown-content li > span,
        .select-dropdown li > span,
        select option {
            color: #000000 !important;
        }

        /* Toast Position Bottom Left with Slide-in Animation */
        #toast-container {
            top: auto !important;
            right: auto !important;
            bottom: 20px !important;
            left: 20px !important;
        }
        .toast {
            animation: toastSlideIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            border-radius: 8px !important;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        @keyframes toastSlideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        /* Top Page Navigation Progress Bar */
        #nprogress-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            height: 3.5px;
            background: linear-gradient(90deg, #00c6ff, #0072ff, #00e5ff);
            box-shadow: 0 0 12px #00e5ff, 0 0 6px #0072ff;
            z-index: 10000;
            transition: width 0.25s ease-out, opacity 0.3s ease;
            pointer-events: none;
            opacity: 0;
        }
        #nprogress-bar.loading {
            opacity: 1;
        }
        #nprogress-bar.done {
            width: 100% !important;
            opacity: 0;
        }

        @keyframes pulse-icon {
            0% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 0.9; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Top Progress Loading Bar -->
    <div id="nprogress-bar"></div>
    
    <!-- Top Navbar -->
    <header>
        <nav class="blue darken-3">
            <div class="nav-wrapper">
                <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>
                <a href="#" id="toggle-sidebar" class="left hide-on-med-and-down waves-effect" style="padding: 0 20px;"><i class="material-icons">menu</i></a>
                <a href="#" class="brand-logo hide-on-large-only" style="font-size: 1.5rem;">Aset BPPN</a>
                <span class="brand-logo hide-on-med-and-down" style="font-size: 1.5rem; padding-left: 20px;">@yield('title', 'Dashboard')</span>
                
                <ul class="right hide-on-med-and-down">
                    @auth
                    <li><a href="#!"><i class="material-icons left">account_circle</i>{{ Auth::user()->name }}</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: none;">@csrf</form>
                        <a href="#!" onclick="document.getElementById('logout-form').submit();"><i class="material-icons left">exit_to_app</i>Logout SSO</a>
                    </li>
                    @endauth
                </ul>
            </div>
        </nav>
    </header>

    <!-- Sidebar -->
    <ul id="slide-out" class="sidenav sidenav-fixed">
        <li>
            <div class="user-view" style="margin-bottom: 0; padding-bottom: 20px;">
                <div class="background blue darken-3"></div>
                <a href="#!"><span class="white-text name" style="font-size: 1.25rem; font-weight: bold; margin-top: 10px;">KPKNL Palembang</span></a>
                <a href="#!"><span class="white-text email" style="font-size: 0.9rem; opacity: 0.9;">Manajemen Aset Eks BPPN</span></a>
            </div>
        </li>
        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><a href="{{ route('dashboard') }}" class="waves-effect"><i class="material-icons">dashboard</i><span>Dashboard</span></a></li>
        <li class="{{ (request()->routeIs('assets.index') || request()->routeIs('assets.show') || request()->routeIs('assets.create') || request()->routeIs('assets.edit')) ? 'active' : '' }}"><a href="{{ route('assets.index') }}" class="waves-effect"><i class="material-icons">inventory_2</i><span>Gudang Aset</span></a></li>
        <li class="{{ request()->routeIs('assets.import.*') ? 'active' : '' }}"><a href="{{ route('assets.import.index') }}" class="waves-effect"><i class="material-icons">cloud_upload</i><span>Impor Aset</span></a></li>
        
        @auth
        @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
        <li class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}"><a href="{{ route('activity-logs.index') }}" class="waves-effect"><i class="material-icons">history</i><span>Log Aktifitas</span></a></li>
        @endif
        <li class="hide-on-large-only"><div class="divider"></div></li>
        <li class="hide-on-large-only"><a class="subheader">Akun</a></li>
        <li class="hide-on-large-only"><a href="#!" onclick="document.getElementById('logout-form').submit();" class="waves-effect"><i class="material-icons">exit_to_app</i><span>Logout SSO</span></a></li>
        @endauth

        <!-- Bottom Element: Logo KPKNL + Jam & Tanggal -->
        <li class="sidebar-footer">
            <img src="{{ asset('images/logo_kpknl.png') }}" alt="Logo KPKNL" class="sidebar-logo" onerror="this.style.display='none'">
            <div class="footer-text" style="margin-bottom: 12px;">
                <strong>KPKNL Palembang</strong><br>
                <span>Sistem Monitoring Laporan</span>
            </div>
            <div class="footer-time-container" style="margin-bottom: 0;">
                <div id="sidebarDate" class="sidebar-date"></div>
                <div id="sidebarTime" class="sidebar-time"></div>
            </div>
        </li>
    </ul>

    <!-- Main Content -->
    <main>
        <div class="container" style="width: 98%; max-width: none; padding-top: 20px;">
            @yield('content')
        </div>
    </main>

    {{-- Global Export Progress Modal --}}
    <div id="modalExportProgress" class="modal" style="max-width: 520px; border-radius: 12px; overflow: hidden;">
        <div class="modal-content center-align" style="padding: 30px 24px 20px 24px;">
            <div id="exportProgressIcon" style="margin-bottom: 12px;">
                <i class="material-icons blue-text text-darken-2" style="font-size: 54px; animation: pulse-icon 1.5s infinite;">file_download</i>
            </div>
            <h5 id="exportProgressTitle" style="font-weight: 700; color: #0d47a1; margin: 0 0 8px 0; font-size: 1.3rem;">
                Sedang Menyiapkan Dokumen...
            </h5>
            <p id="exportProgressStep" class="grey-text text-darken-1" style="font-size: 0.95rem; margin: 0 0 20px 0;">
                Memproses data aset dan mengompilasi layout dokumen.
            </p>

            <!-- Progress Bar -->
            <div class="progress blue lighten-4" style="height: 10px; border-radius: 5px; margin-bottom: 8px;">
                <div id="exportProgressBar" class="determinate blue darken-2" style="width: 15%; transition: width 0.3s ease-out; border-radius: 5px;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #757575; font-weight: 500;">
                <span id="exportProgressStatus">Menghubungkan ke server...</span>
                <span id="exportProgressPercent">15%</span>
            </div>
        </div>
        <div class="modal-footer" style="background: #fafafa; padding: 10px 24px; justify-content: center; display: flex;">
            <button type="button" id="btnCancelExport" class="modal-close btn-flat grey-text text-darken-1 waves-effect" style="font-weight: 500;">Batal</button>
        </div>
    </div>

    <!-- Materialize JS -->
    <script src="{{ asset('js/materialize.min.js') }}"></script>
    <script>
        // Page Navigation Progress Bar Logic
        let progressInterval = null;
        function startPageProgress() {
            const bar = document.getElementById('nprogress-bar');
            if (!bar) return;
            bar.classList.remove('done');
            bar.classList.add('loading');
            bar.style.width = '20%';
            
            clearInterval(progressInterval);
            progressInterval = setInterval(() => {
                let current = parseFloat(bar.style.width) || 20;
                if (current < 85) {
                    bar.style.width = (current + Math.random() * 12) + '%';
                }
            }, 180);
        }

        function completePageProgress() {
            const bar = document.getElementById('nprogress-bar');
            if (!bar) return;
            clearInterval(progressInterval);
            bar.style.width = '100%';
            setTimeout(() => {
                bar.classList.add('done');
                bar.classList.remove('loading');
                setTimeout(() => {
                    bar.style.width = '0%';
                }, 300);
            }, 120);
        }

        // Attach to navigation link clicks and forms
        document.addEventListener('click', function(e) {
            let target = e.target.closest('a');
            if (!target) return;
            let href = target.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:') || target.getAttribute('target') === '_blank' || target.hasAttribute('download') || target.classList.contains('modal-trigger') || target.classList.contains('dropdown-trigger')) {
                return;
            }
            startPageProgress();
        });

        document.addEventListener('submit', function(e) {
            if (e.target && !e.target.hasAttribute('target') && !e.target.id.includes('Upload')) {
                startPageProgress();
            }
        });

        window.addEventListener('pageshow', function() {
            completePageProgress();
        });

        // Global Export Function with Progress Modal
        let exportAbortController = null;
        window.startExportWithProgress = function(url, title, filename) {
            const modalEl = document.getElementById('modalExportProgress');
            const instance = M.Modal.getInstance(modalEl) || M.Modal.init(modalEl, { dismissible: false });

            const bar = document.getElementById('exportProgressBar');
            const percentEl = document.getElementById('exportProgressPercent');
            const statusEl = document.getElementById('exportProgressStatus');
            const titleEl = document.getElementById('exportProgressTitle');
            const stepEl = document.getElementById('exportProgressStep');
            const iconEl = document.getElementById('exportProgressIcon');
            const cancelBtn = document.getElementById('btnCancelExport');

            titleEl.textContent = title || 'Mengekspor Dokumen...';
            stepEl.textContent = 'Memproses data dan mengompilasi layout...';
            statusEl.textContent = 'Menghubungkan ke server...';
            bar.className = 'determinate blue darken-2';
            bar.style.width = '15%';
            percentEl.textContent = '15%';
            iconEl.innerHTML = '<i class="material-icons blue-text text-darken-2" style="font-size: 54px; animation: pulse-icon 1.5s infinite;">file_download</i>';
            cancelBtn.style.display = 'inline-block';
            cancelBtn.textContent = 'Batal';

            instance.open();

            let progress = 15;
            const timer = setInterval(() => {
                if (progress < 45) {
                    progress += 6;
                    statusEl.textContent = 'Mengumpulkan data aset & lampiran...';
                    stepEl.textContent = 'Memuat database dan riwayat aset...';
                } else if (progress < 75) {
                    progress += 3;
                    statusEl.textContent = 'Merender layout & format dokumen...';
                    stepEl.textContent = 'Mengompilasi halaman dan tabel...';
                } else if (progress < 90) {
                    progress += 1;
                    statusEl.textContent = 'Menyusun berkas dokumen final...';
                }
                bar.style.width = progress + '%';
                percentEl.textContent = Math.round(progress) + '%';
            }, 250);

            exportAbortController = new AbortController();

            fetch(url, { signal: exportAbortController.signal })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal mengunduh dokumen (HTTP ' + response.status + ')');
                    }
                    return response.blob();
                })
                .then(blob => {
                    clearInterval(timer);
                    bar.style.width = '100%';
                    percentEl.textContent = '100%';
                    statusEl.textContent = 'Dokumen Selesai!';
                    stepEl.textContent = 'Membuka / mengunduh file dokumen...';
                    iconEl.innerHTML = '<i class="material-icons green-text text-darken-2" style="font-size: 54px;">check_circle</i>';
                    cancelBtn.style.display = 'none';

                    const blobUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = blobUrl;
                    a.download = filename || 'Dokumen_Aset.pdf';
                    document.body.appendChild(a);
                    a.click();

                    setTimeout(() => {
                        window.URL.revokeObjectURL(blobUrl);
                        document.body.removeChild(a);
                        M.toast({html: '<i class="material-icons left">check_circle</i> Ekspor dokumen berhasil diunduh.', classes: 'green darken-2 white-text'});
                        instance.close();
                    }, 800);
                })
                .catch(err => {
                    clearInterval(timer);
                    if (err.name === 'AbortError') {
                        instance.close();
                        return;
                    }
                    bar.className = 'determinate red darken-2';
                    bar.style.width = '100%';
                    percentEl.textContent = '!';
                    statusEl.textContent = 'Gagal Mengunduh';
                    stepEl.textContent = err.message || 'Terjadi kesalahan saat memproses ekspor.';
                    iconEl.innerHTML = '<i class="material-icons red-text text-darken-2" style="font-size: 54px;">error</i>';
                    cancelBtn.textContent = 'Tutup';
                    cancelBtn.style.display = 'inline-block';
                });

            cancelBtn.onclick = function() {
                clearInterval(timer);
                if (exportAbortController) {
                    exportAbortController.abort();
                }
                instance.close();
            };
        };

        function updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            const clockEl = document.getElementById('sidebarTime');
            const dateEl = document.getElementById('sidebarDate');
            
            if(clockEl) clockEl.textContent = `${hours}.${minutes}.${seconds}`;
            if(dateEl) dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            var elems = document.querySelectorAll('.sidenav');
            M.Sidenav.init(elems, {});
            
            var elemsModal = document.querySelectorAll('.modal');
            M.Modal.init(elemsModal);

            // Toggle Mini Sidebar
            var toggleBtn = document.getElementById('toggle-sidebar');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.body.classList.toggle('sidebar-mini');
                });
            }
            
            updateClock();
            setInterval(updateClock, 1000);
            completePageProgress();
        });
    </script>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                M.toast({html: '<i class="material-icons left">check_circle</i> {{ session('success') }}', classes: 'green darken-2 white-text'});
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                M.toast({html: '<i class="material-icons left">error</i> {{ session('error') }}', classes: 'red darken-2 white-text'});
            });
        </script>
    @endif

    @auth
    <script>
        // Single Sign-Out Real-time Sync: Check session when returning to this tab
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                fetch('{{ route("dashboard") }}', { method: 'HEAD', credentials: 'same-origin', cache: 'no-store' })
                    .then(function(res) {
                        if (res.redirected || res.status === 401) {
                            window.location.reload();
                        }
                    }).catch(function() {});
            }
        });
    </script>
    @endauth

    @stack('scripts')
</body>
</html>
