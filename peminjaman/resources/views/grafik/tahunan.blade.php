@extends('layouts.app')

@section('title', 'Grafik Tahunan')

@section('content')
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <a href="{{ route('grafik.index') }}" class="btn btn-outline">
        ← Kembali ke Informasi Grafik
    </a>
</div>

<div class="grafik-hero-header">
    <h1>GRAFIK TAHUNAN</h1>
    <p>Pilih kategori grafik tahunan yang ingin Anda visualisasikan.</p>
</div>

<div class="grafik-menu-grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
    <!-- Submenu 1: Pelelangan -->
    <a href="{{ route('grafik.tahunan.pelelangan') }}" class="grafik-menu-card">
        <div class="grafik-menu-icon icon-indigo">
            📊
        </div>
        <div class="grafik-menu-info">
            <h3>Grafik Tahunan Pelelangan</h3>
            <p>Perbandingan volume tahunan untuk Risalah Minuta, TAP, dan Batal.</p>
        </div>
        <div class="grafik-menu-arrow">→</div>
    </a>

    <!-- Submenu 2: Peminjaman -->
    <a href="{{ route('grafik.tahunan.peminjaman') }}" class="grafik-menu-card">
        <div class="grafik-menu-icon icon-rose">
            📑
        </div>
        <div class="grafik-menu-info">
            <h3>Grafik Tahunan Peminjaman</h3>
            <p>Total transaksi peminjaman berkas seluruh pengguna dari tahun ke tahun.</p>
        </div>
        <div class="grafik-menu-arrow">→</div>
    </a>
</div>
@endsection
