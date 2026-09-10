@extends('layouts.app')

@section('title', 'Informasi Grafik & Statistik')

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : (Auth::user()->isPelelang() ? route('pelelang.dashboard') : route('peminjam.dashboard')) }}" class="btn btn-outline">
        ← Kembali ke Dashboard
    </a>
</div>

<div class="grafik-hero-header">
    <h1>INFORMASI GRAFIK</h1>
    <p>Visualisasi data dan analisis statistik risalah lelang serta aktivitas peminjaman berkas pada KPKNL.</p>
</div>

<div class="grafik-menu-grid">
    <!-- Menu 1: Grafik Bulanan -->
    <a href="{{ route('grafik.bulanan') }}" class="grafik-menu-card">
        <div class="grafik-menu-icon icon-blue">
            📅
        </div>
        <div class="grafik-menu-info">
            <h3>Grafik Bulanan</h3>
            <p>Tren bulanan pelelangan (Minuta, TAP, Batal) dan peminjaman berkas.</p>
        </div>
        <div class="grafik-menu-arrow">→</div>
    </a>

    <!-- Menu 2: Grafik Tahunan -->
    <a href="{{ route('grafik.tahunan') }}" class="grafik-menu-card">
        <div class="grafik-menu-icon icon-indigo">
            📈
        </div>
        <div class="grafik-menu-info">
            <h3>Grafik Tahunan</h3>
            <p>Perbandingan volume pelelangan dan riwayat peminjaman antar tahun.</p>
        </div>
        <div class="grafik-menu-arrow">→</div>
    </a>

    <!-- Menu 3: Grafik Pelelang -->
    <a href="{{ route('grafik.pelelang') }}" class="grafik-menu-card">
        <div class="grafik-menu-icon icon-emerald">
            ⚖️
        </div>
        <div class="grafik-menu-info">
            <h3>Grafik Pelelang</h3>
            <p>Statistik produktivitas bulanan dan tahunan per Pejabat Lelang.</p>
        </div>
        <div class="grafik-menu-arrow">→</div>
    </a>

    <!-- Menu 4: Grafik Peminjam -->
    <a href="{{ route('grafik.peminjam') }}" class="grafik-menu-card">
        <div class="grafik-menu-icon icon-rose">
            📚
        </div>
        <div class="grafik-menu-info">
            <h3>Grafik Peminjam</h3>
            <p>Statistik frekuensi peminjaman berkas fisik per nama pengguna.</p>
        </div>
        <div class="grafik-menu-arrow">→</div>
    </a>
</div>
@endsection
