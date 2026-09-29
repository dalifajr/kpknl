@extends('layouts.app')

@section('title', 'Dashboard User | SSO KPKNL Palembang')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="mb-1 fw-bold text-dark">Portal Aplikasi SSO</h3>
        <p class="text-muted mb-0">Selamat datang, <strong>{{ auth()->user()->name }}</strong>. Klik ikon aplikasi untuk langsung masuk tanpa login ulang.</p>
    </div>
</div>

@include('components.app-launcher', ['apps' => $assignedApps])
@endsection

