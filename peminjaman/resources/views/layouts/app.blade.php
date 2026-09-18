<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Pengelolaan Risalah Lelang') - KPKNL</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/app_custom.css') }}">

    <!-- Chart.js for visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('styles')
</head>
<body>
    @include('components.navbar')

    <main class="main-content">
        @include('components.alerts')
        @yield('content')
    </main>

    <footer style="text-align: center; padding: 20px; font-size: 0.85rem; color: var(--text-muted); background: #ffffff; border-top: 1px solid var(--border-color); margin-top: auto;">
        &copy; {{ date('Y') }} Kantor Pelayanan Kekayaan Negara dan Lelang (KPKNL). All rights reserved.
    </footer>

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
