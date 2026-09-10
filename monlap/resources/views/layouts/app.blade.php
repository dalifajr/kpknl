<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MonLap - Sistem Monitoring Laporan</title>
    <!-- Material Font Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS with Cache Buster -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">
    @stack('styles')
    <style>
        .sidebar-footer {
            margin-top: auto;
            padding: 16px;
            border-top: 1px solid var(--divider);
            text-align: center;
            background-color: var(--surface-color);
            transition: var(--transition);
        }
        .footer-time-container {
            margin-bottom: 16px;
            color: var(--text-secondary);
        }
        .sidebar-date {
            font-size: 13px;
            font-weight: 500;
        }
        .sidebar-time {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            font-family: monospace;
            letter-spacing: 1px;
            margin-top: 4px;
        }
        .sidebar-logo {
            width: 64px;
            height: auto;
            margin-bottom: 8px;
            transition: var(--transition);
        }
        .footer-text {
            font-size: 12px;
            color: var(--text-secondary);
            line-height: 1.4;
        }
        .footer-text strong {
            color: var(--text-primary);
        }
        
        .sidebar.collapsed .sidebar-footer {
            padding: 16px 8px;
        }
        .sidebar.collapsed .footer-time-container,
        .sidebar.collapsed .footer-text {
            display: none;
        }
        .sidebar.collapsed .sidebar-logo {
            width: 40px;
            margin-bottom: 0;
        }

        /* Custom Pagination Styling */
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            gap: 4px;
            margin-bottom: 0;
        }
        .page-link {
            position: relative;
            display: block;
            padding: 8px 16px;
            color: var(--primary);
            background-color: var(--surface-color);
            border: 1px solid var(--divider);
            text-decoration: none;
        }
        .page-link:hover {
            background-color: var(--hover-bg);
            border-color: var(--primary);
        }
        .page-item.active .page-link {
            color: #fff;
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .page-item.disabled .page-link {
            color: var(--text-secondary);
            pointer-events: none;
            background-color: var(--hover-bg);
            border-color: var(--divider);
        }
        .d-flex.justify-content-between.flex-fill.d-sm-none {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        .d-none.flex-sm-fill.d-sm-flex.align-items-sm-center.justify-content-sm-between {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .small.text-muted {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 0;
        }
        .fw-semibold {
            font-weight: 600;
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <!-- Top Navigation Loading Progress Bar -->
    <div class="top-progress-bar" id="top-progress-bar">
        <div class="top-progress-bar-indicator"></div>
    </div>

    <div class="app-container" id="appContainer">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header" onclick="handleSidebarHeaderClick()" title="Toggle Sidebar">
                <div class="logo-full-wrapper" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="material-icons" style="font-size: 22px;">assignment_turned_in</i>
                        <span class="logo-text" style="font-weight: 500; font-size: 18px;">MonLap</span>
                    </div>
                    <button type="button" class="sidebar-toggle-btn icon-btn" onclick="event.stopPropagation(); toggleSidebar();" title="Perkecil Sidebar" style="color: white; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: none; background: rgba(255,255,255,0.15); cursor: pointer;">
                        <i class="material-icons" style="font-size: 18px;">chevron_left</i>
                    </button>
                </div>
                <i class="material-icons logo-collapsed-icon" title="Perluas Sidebar" style="font-size: 24px; cursor: pointer;">menu</i>
            </div>
            <ul class="nav-list" style="display: flex; flex-direction: column; flex: 1;">
                <li>
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                        <i class="material-icons">dashboard</i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('calendar.index') }}" class="nav-item {{ request()->routeIs('calendar.*') ? 'active' : '' }}" title="Kalender">
                        <i class="material-icons">calendar_month</i>
                        <span class="nav-text">Kalender</span>
                    </a>
                </li>
                
                @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin')
                <li>
                    <a href="{{ route('tasks.index') }}" class="nav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}" title="Tugas Induk">
                        <i class="material-icons">assignment</i>
                        <span class="nav-text">Tugas Induk</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('reviews.index') }}" class="nav-item {{ request()->routeIs('reviews.*') ? 'active' : '' }}" title="Daftar Laporan">
                        <i class="material-icons">fact_check</i>
                        <span class="nav-text">Daftar Laporan</span>
                    </a>
                </li>
                @endif
                
                <li>
                    <a href="{{ route('user-tasks.index') }}" class="nav-item {{ request()->routeIs('user-tasks.*') ? 'active' : '' }}" title="Todo-List Saya">
                        <i class="material-icons">list_alt</i>
                        <span class="nav-text">Todo-List Saya</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <div class="footer-time-container">
                    <div id="sidebarDate" class="sidebar-date"></div>
                    <div id="sidebarTime" class="sidebar-time"></div>
                </div>
                <img src="{{ asset('images/logo.png') }}" alt="Logo KPKNL" class="sidebar-logo">
                <div class="footer-text">
                    <strong>KPKNL Palembang</strong><br>
                    <span>Sistem Monitoring Laporan</span>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <header class="topbar">
                <div class="d-flex align-center">
                    <button class="menu-toggle" onclick="toggleSidebar()" type="button" title="Perluas / Perkecil Sidebar">
                        <i class="material-icons">menu</i>
                    </button>
                    <div class="topbar-title">@yield('title', 'Dashboard')</div>
                </div>
                <div class="topbar-actions">
                    <button type="button" class="icon-btn" id="themeToggleBtn" title="Toggle Dark Mode" style="margin-right: 8px;">
                        <i class="material-icons" id="themeIcon">dark_mode</i>
                    </button>
                    @php
                        $unreadCount = auth()->user()->monlapNotifications()->where('is_read', false)->count();
                    @endphp
                    <a href="{{ route('notifications.index') }}" class="icon-btn" title="Notification Hub" style="position: relative; text-decoration: none;">
                        <i class="material-icons">notifications</i>
                        @if($unreadCount > 0)
                            <span style="position: absolute; top: 4px; right: 4px; background: #FF5252; color: white; font-size: 10px; font-weight: bold; padding: 2px 4px; border-radius: 8px; line-height: 1;">
                                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                            </span>
                        @endif
                    </a>
                    <div style="font-weight: 500; margin-left: 16px;">
                        {{ auth()->user()->name }}
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="icon-btn" title="Logout">
                            <i class="material-icons">exit_to_app</i>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Content -->
            <div class="content-wrapper page-enter" id="contentWrapper">
                <nav class="breadcrumbs" aria-label="breadcrumb" style="margin-bottom: 16px; font-size: 14px; font-weight: 500;">
                    <ol style="list-style: none; padding: 0; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <li><a href="{{ route('dashboard') }}" style="color: var(--primary); text-decoration: none;"><i class="material-icons" style="font-size: 16px; vertical-align: middle;">home</i> Beranda</a></li>
                        @if(!request()->routeIs('dashboard'))
                            <li style="color: var(--text-secondary);"><i class="material-icons" style="font-size: 16px; vertical-align: middle;">chevron_right</i></li>
                            <li style="color: var(--text-secondary);" aria-current="page">@yield('title', 'Halaman')</li>
                        @endif
                    </ol>
                </nav>
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    
    <script>
        // Dark Mode Logic
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        
        function applyTheme(isDark) {
            if (isDark) {
                document.documentElement.setAttribute('data-theme', 'dark');
                if (themeIcon) themeIcon.textContent = 'light_mode';
            } else {
                document.documentElement.removeAttribute('data-theme');
                if (themeIcon) themeIcon.textContent = 'dark_mode';
            }
        }
        
        const savedTheme = localStorage.getItem('monlapTheme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        let isDarkMode = savedTheme === 'dark' || (!savedTheme && prefersDark);
        
        applyTheme(isDarkMode);
        
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                isDarkMode = !isDarkMode;
                localStorage.setItem('monlapTheme', isDarkMode ? 'dark' : 'light');
                applyTheme(isDarkMode);
            });
        }

        function handleSidebarHeaderClick() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.classList.contains('collapsed')) {
                toggleSidebar();
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const container = document.getElementById('appContainer');
            
            if (window.innerWidth > 768) {
                // Desktop: Collapse / Expand sidebar
                if (sidebar) sidebar.classList.toggle('collapsed');
                if (container) container.classList.toggle('sidebar-collapsed');
                const isCollapsed = sidebar && sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
            } else {
                // Mobile: Slide in / out drawer
                if (sidebar) sidebar.classList.toggle('open');
                if (overlay) overlay.classList.toggle('active');
            }

            // Trigger window resize and calendar update after transition completes
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
                if (typeof calendar !== 'undefined' && calendar) {
                    calendar.updateSize();
                }
            }, 320);
        }

        // Restore desktop collapsed state on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth > 768 && localStorage.getItem('sidebarCollapsed') === 'true') {
                const sidebar = document.getElementById('sidebar');
                const container = document.getElementById('appContainer');
                if (sidebar) sidebar.classList.add('collapsed');
                if (container) container.classList.add('sidebar-collapsed');
            }
        });

        // Clock logic
        function updateClock() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateEl = document.getElementById('sidebarDate');
            const timeEl = document.getElementById('sidebarTime');
            if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', options);
            if (timeEl) timeEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        setInterval(updateClock, 1000);
        document.addEventListener('DOMContentLoaded', updateClock);
    </script>

    <!-- Global Toast Notification (Android 9 Style) -->
    @if(session('success') || session('error') || $errors->any())
    <div id="androidToast" class="android-toast">
        @if(session('success'))
            <i class="material-icons" style="color: #81C784;">check_circle</i>
            <span>{{ session('success') }}</span>
        @elseif(session('error'))
            <i class="material-icons" style="color: #E57373;">error</i>
            <span>{{ session('error') }}</span>
        @elseif($errors->any())
            <i class="material-icons" style="color: #E57373;">error</i>
            <span>{{ $errors->first() }}</span>
        @endif
    </div>
    
    <style>
        .android-toast {
            position: fixed;
            bottom: 32px;
            left: 32px;
            background-color: #323232;
            color: #FFFFFF;
            padding: 14px 24px;
            border-radius: 4px;
            box-shadow: 0 3px 5px -1px rgba(0,0,0,.2), 0 6px 10px 0 rgba(0,0,0,.14), 0 1px 18px 0 rgba(0,0,0,.12);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            font-size: 14px;
            font-family: 'Roboto', sans-serif;
            opacity: 0;
            transform: translateY(20px);
            animation: slideUpFade 0.3s forwards, slideDownFade 0.3s forwards 4s;
        }

        @keyframes slideUpFade {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDownFade {
            to {
                opacity: 0;
                transform: translateY(20px);
                visibility: hidden;
            }
        }
    </style>
    @endif
    <script>
        // Global Top Progress Bar Manager
        window.TopProgress = {
            timer: null,
            start: function () {
                const bar = document.getElementById('top-progress-bar');
                const wrapper = document.getElementById('contentWrapper');
                if (bar) bar.classList.add('active');
                if (wrapper) {
                    wrapper.style.opacity = '0.75';
                    wrapper.style.transition = 'opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1)';
                }
                clearTimeout(this.timer);
                // Safety timeout to auto-clear in case navigation is cancelled/downloaded/stays on page
                this.timer = setTimeout(() => {
                    this.done();
                }, 4000);
            },
            done: function () {
                clearTimeout(this.timer);
                const bar = document.getElementById('top-progress-bar');
                const wrapper = document.getElementById('contentWrapper');
                if (bar) bar.classList.remove('active');
                if (wrapper) {
                    wrapper.style.opacity = '1';
                }
            }
        };

        // Lifecycle Events: Always complete on page load or bfcache restore
        window.addEventListener('load', () => window.TopProgress.done());
        window.addEventListener('pageshow', () => window.TopProgress.done());
        document.addEventListener('DOMContentLoaded', () => window.TopProgress.done());

        // Page Navigation Transition
        document.addEventListener('DOMContentLoaded', function () {
            // Attach listener to all internal links
            document.addEventListener('click', function (e) {
                // Ignore non-primary clicks (middle click, right click)
                if (e.button !== 0) return;

                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                const target = link.getAttribute('target');

                // Validate if it's an actual navigating link
                if (
                    href && 
                    href !== '#' && 
                    !href.startsWith('#') && 
                    !href.startsWith('javascript:') && 
                    !link.hasAttribute('data-no-progress') && 
                    !link.hasAttribute('download') && 
                    (!target || target === '_self') &&
                    !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey
                ) {
                    // Check if it's not pointing to the exact same URL + hash
                    try {
                        const targetUrl = new URL(link.href, window.location.origin);
                        if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search && targetUrl.hash) {
                            return; // Same page anchor jump
                        }
                    } catch (err) {}

                    window.TopProgress.start();
                }
            });

            // Form submit transition (only for non-ajax regular submit)
            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (
                    !form.hasAttribute('data-no-progress') && 
                    !form.hasAttribute('data-ajax') && 
                    !form.getAttribute('target') &&
                    !e.defaultPrevented
                ) {
                    window.TopProgress.start();
                }
            });
        });

        // Universal Material Ripple Wave Effect
        document.addEventListener('mousedown', function (e) {
            let target = e.target.closest('.btn, .nav-item, .icon-btn, .ripple-surface, .fc-event, .color-radio-label');
            if (!target) return;
            
            const rect = target.getBoundingClientRect();
            const ripple = document.createElement('span');
            const diameter = Math.max(rect.width, rect.height) * 1.5;
            const radius = diameter / 2;
            
            ripple.style.width = ripple.style.height = `${diameter}px`;
            ripple.style.left = `${e.clientX - rect.left - radius}px`;
            ripple.style.top = `${e.clientY - rect.top - radius}px`;
            
            // Check if element has light background to use dark ripple
            const isLightBg = target.classList.contains('btn-flat') || 
                              target.classList.contains('nav-item') || 
                              target.classList.contains('color-radio-label') ||
                              target.closest('.sidebar');
            
            ripple.className = isLightBg ? 'ripple-wave dark' : 'ripple-wave';
            
            const existingRipple = target.querySelector('.ripple-wave');
            if (existingRipple) existingRipple.remove();
            
            target.style.position = target.style.position || 'relative';
            target.style.overflow = 'hidden';
            target.appendChild(ripple);
            
            setTimeout(() => { ripple.remove(); }, 600);
        });

        // MD2 Form Input - detect value for floating label
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('md-input')) {
                if (e.target.value) {
                    e.target.classList.add('has-value');
                } else {
                    e.target.classList.remove('has-value');
                }
            }
        });
        
        document.querySelectorAll('.md-input').forEach(input => {
            if (input.value) input.classList.add('has-value');
        });

        // MD2 Custom Modal System
        function showMDModal(title, content, confirmCallback, isDestructive = false) {
            let backdrop = document.getElementById('md-modal-backdrop');
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.id = 'md-modal-backdrop';
                backdrop.className = 'md-modal-backdrop';
                backdrop.innerHTML = `
                    <div class="md-modal">
                        <div class="md-modal-title" id="md-modal-title"></div>
                        <div class="md-modal-content" id="md-modal-content"></div>
                        <div class="md-modal-actions">
                            <button type="button" class="btn btn-flat" id="md-modal-cancel">Batal</button>
                            <button type="button" class="btn btn-flat" id="md-modal-confirm">Ya</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(backdrop);
            }
            
            document.getElementById('md-modal-title').innerText = title;
            document.getElementById('md-modal-content').innerText = content;
            
            const confirmBtn = document.getElementById('md-modal-confirm');
            confirmBtn.style.color = isDestructive ? '#F44336' : 'var(--primary)';
            
            // Re-clone to remove old listeners
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            const cancelBtn = document.getElementById('md-modal-cancel');
            const newCancelBtn = cancelBtn.cloneNode(true);
            cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
            
            newCancelBtn.addEventListener('click', hideMDModal);
            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) hideMDModal();
            });
            
            newConfirmBtn.addEventListener('click', () => {
                if (confirmCallback) confirmCallback();
                hideMDModal();
            });
            
            setTimeout(() => {
                backdrop.classList.add('show');
            }, 10);
        }

        function hideMDModal() {
            const backdrop = document.getElementById('md-modal-backdrop');
            if (backdrop) {
                backdrop.classList.remove('show');
            }
        }

        // MD2 Toast Notification
        function showToast(message, type = 'info') {
            let container = document.getElementById('md-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'md-toast-container';
                container.className = 'md-toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = `md-toast ${type}`;
            
            let icon = 'info';
            if (type === 'success') icon = 'check_circle';
            if (type === 'error') icon = 'error';

            toast.innerHTML = `<i class="material-icons">${icon}</i> <span>${message}</span>`;
            container.appendChild(toast);

            // Animate in
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);

            // Animate out and remove
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3500);
        }
    </script>
</body>
</html>
