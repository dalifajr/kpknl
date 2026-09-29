@extends('layouts.app')

@section('title', 'Dashboard Superadmin | SSO KPKNL Palembang')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Dashboard Superadmin</h3>
        <p class="text-muted mb-0">Selamat datang kembali, <strong>{{ auth()->user()->name }}</strong>. Anda memiliki kendali penuh atas sistem SSO.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-2">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah User Baru
        </a>
    </div>
</div>

@if(auth()->user()->username === 'mardanus' && Hash::check('admin123', auth()->user()->password))
    <div class="alert alert-expressive alert-expressive-danger d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 text-danger"></i>
            <div>
                <strong>Peringatan Keamanan:</strong> Anda masih menggunakan password default (<code>admin123</code>). Segera perbarui password Anda demi keamanan sistem!
            </div>
        </div>
        <a href="{{ route('profile.show') }}" class="btn btn-sm btn-danger text-white rounded-pill px-3 ms-2">Ganti Password Sekarang</a>
    </div>
@endif



@include('components.app-launcher', ['apps' => $applications])
@endsection

