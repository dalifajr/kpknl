@extends('layouts.app')

@section('title', 'Dashboard Admin | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Dashboard Admin Aplikasi</h3>
        <p class="text-muted mb-0">Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Anda memiliki akses pengawasan ke aplikasi yang di-assign oleh Superadmin.</p>
    </div>
</div>


@include('components.app-launcher', ['apps' => $assignedApps])
@endsection

