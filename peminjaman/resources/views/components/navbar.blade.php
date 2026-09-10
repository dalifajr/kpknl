<nav class="app-navbar">
    <a href="{{ url('/') }}" class="brand">
        <img src="{{ asset('images/logo.png') }}" alt="KPKNL Logo" onerror="this.onerror=null; this.src='{{ asset('logo.png') }}';">
        <span>Sistem Risalah KPKNL</span>
    </a>

    @auth
        <ul class="nav-links">
            @if (Auth::user()->isAdmin())
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('admin.validasi.index') }}" class="{{ request()->routeIs('admin.validasi.*') ? 'active' : '' }}">Validasi Risalah</a></li>
                <li><a href="{{ route('admin.peminjaman.index') }}" class="{{ request()->routeIs('admin.peminjaman.*') ? 'active' : '' }}">Peminjaman</a></li>
                <li><a href="{{ route('admin.pengembalian.index') }}" class="{{ request()->routeIs('admin.pengembalian.*') ? 'active' : '' }}">Pengembalian</a></li>
            @elseif (Auth::user()->isPelelang())
                <li><a href="{{ route('pelelang.dashboard') }}" class="{{ request()->routeIs('pelelang.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('pelelang.risalah.create') }}" class="{{ request()->routeIs('pelelang.risalah.create') ? 'active' : '' }}">Tambah Risalah</a></li>
                <li><a href="{{ route('pelelang.pending') }}" class="{{ request()->routeIs('pelelang.pending') ? 'active' : '' }}">Status Pending</a></li>
                <li><a href="{{ route('pelelang.revisi.index') }}" class="{{ request()->routeIs('pelelang.revisi.*') ? 'active' : '' }}">Revisi</a></li>
                <li><a href="{{ route('pelelang.history') }}" class="{{ request()->routeIs('pelelang.history') ? 'active' : '' }}">Riwayat</a></li>
            @elseif (Auth::user()->isPeminjam())
                <li><a href="{{ route('peminjam.dashboard') }}" class="{{ request()->routeIs('peminjam.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('peminjam.katalog') }}" class="{{ request()->routeIs('peminjam.katalog') ? 'active' : '' }}">Katalog Risalah</a></li>
                <li><a href="{{ route('peminjam.pinjaman') }}" class="{{ request()->routeIs('peminjam.pinjaman') ? 'active' : '' }}">Pinjaman Saya</a></li>
            @endif

            <!-- Common Menus -->
            <li><a href="{{ route('risalah.minuta') }}" class="{{ request()->routeIs('risalah.minuta') ? 'active' : '' }}">Minuta</a></li>
            <li><a href="{{ route('risalah.tap') }}" class="{{ request()->routeIs('risalah.tap') ? 'active' : '' }}">TAP</a></li>
            <li><a href="{{ route('risalah.batal') }}" class="{{ request()->routeIs('risalah.batal') ? 'active' : '' }}">Batal</a></li>
            <li><a href="{{ route('grafik.index') }}" class="{{ request()->routeIs('grafik.*') ? 'active' : '' }}">Grafik</a></li>

            <li>
                <div class="user-badge">
                    <span>{{ Auth::user()->username }}</span>
                    <span class="role-tag role-{{ Auth::user()->role }}">{{ Auth::user()->role }}</span>
                </div>
            </li>
            <li>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 10px;">Keluar</button>
                </form>
            </li>
        </ul>
    @else
        <ul class="nav-links">
            <li><a href="{{ route('login') }}">Masuk</a></li>
            <li><a href="{{ route('register') }}">Daftar</a></li>
        </ul>
    @endauth
</nav>
