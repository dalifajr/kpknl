@extends('layouts.app')

@section('title', 'Grafik Bulanan')

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <a href="{{ route('grafik.index') }}" class="btn btn-outline">
        ← Kembali ke Informasi Grafik
    </a>
</div>

<div class="grafik-hero-header">
    <h1>GRAFIK BULANAN</h1>
    <p>Pilih kategori grafik bulanan yang ingin Anda visualisasikan.</p>
</div>

<div class="grafik-menu-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
    <!-- Submenu 1: Pelelangan -->
    <a href="{{ route('grafik.bulanan.pelelangan') }}" class="grafik-menu-card">
        <div class="grafik-menu-icon icon-blue">
            🏷️
        </div>
        <div class="grafik-menu-info">
            <h3>Grafik Bulanan Pelelangan</h3>
            <p>Tren data bulanan per tahun untuk Risalah Minuta, TAP, dan Batal.</p>
        </div>
        <div class="grafik-menu-arrow">→</div>
    </a>

    <!-- Submenu 2: Peminjaman -->
    <a href="{{ route('grafik.bulanan.peminjaman') }}" class="grafik-menu-card">
        <div class="grafik-menu-icon icon-emerald">
            📖
        </div>
        <div class="grafik-menu-info">
            <h3>Grafik Bulanan Peminjaman</h3>
            <p>Total transaksi peminjaman berkas seluruh pengguna per bulan.</p>
        </div>
        <div class="grafik-menu-arrow">→</div>
    </a>
</div>
@endsection
