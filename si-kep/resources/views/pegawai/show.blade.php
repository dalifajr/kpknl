@extends('layouts.app')

@section('title', 'Profil Pegawai: ' . $pegawai->display_name . ' — SI-KEP KPKNL Palembang')
@section('hero-title', $pegawai->display_name)
@section('hero-subtitle', 'NIP: ' . ($pegawai->nip ?: '-') . ' &bull; ' . ($pegawai->nama_jabatan_raw ?: 'Pegawai KPKNL Palembang'))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="mb-3">
            <a href="{{ route('pegawai.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Direktori Pegawai
            </a>
        </div>
        <div class="card card-custom overflow-hidden shadow-sm">
            @include('pegawai.detail_modal', ['pegawai' => $pegawai])
        </div>
    </div>
</div>
@endsection
