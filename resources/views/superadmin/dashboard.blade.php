@extends('layouts.app')

@section('title', 'Dashboard Superadmin')

@section('content')
<div class="container mt-5">

    {{-- Hero Section --}}
    <div class="p-5 mb-5 rounded-4 shadow-lg dashboard-hero text-center">
        <h1 class="display-5 fw-bold mb-3">
            <i class="bi bi-person-gear me-2"></i>Dashboard Superadmin
        </h1>
        <p class="fs-5 mb-0">
            Selamat {{ now()->format('H') < 12 ? 'pagi' : (now()->format('H') < 18 ? 'siang' : 'malam') }},
            <strong>{{ Auth::user()->name_user }}</strong> 👋<br>
            Kelola sistem monitor digital dengan kontrol penuh.
        </p>
    </div>

    {{-- Konten Card --}}
    <div class="row g-3 justify-content-center">

        {{-- Kelola User --}}
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card dashboard-card text-center shadow-sm border-0 rounded-4 p-4 h-100">
                <div class="icon-circle bg-success-subtle text-success mx-auto mb-3">
                    <i class="bi bi-people-fill fs-1"></i>
                </div>
                <h5 class="card-title fw-semibold">Manajemen User</h5>
                <p class="card-text">Tambah, ubah, dan hapus akun pengguna serta peran mereka.</p>
                <a href="{{ route('user.index') }}" class="btn btn-success mt-3 shadow-sm px-4 py-2">
                    <i class="bi bi-arrow-right-circle me-1"></i> Kelola User
                </a>
            </div>
        </div>

        {{-- Kelola Departemen --}}
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card dashboard-card text-center shadow-sm border-0 rounded-4 p-4 h-100">
                <div class="icon-circle bg-success-subtle text-success mx-auto mb-3">
                    <i class="bi bi-diagram-3 fs-1"></i>
                </div>
                <h5 class="card-title fw-semibold">Manajemen Departemen</h5>
                <p class="card-text">Kelola struktur departemen untuk pengaturan konten yang efisien.</p>
                <a href="{{ route('department.index') }}" class="btn btn-success mt-3 shadow-sm px-4 py-2">
                    <i class="bi bi-arrow-right-circle me-1"></i> Kelola Departemen
                </a>
            </div>
</div>

    </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/superadmin.css') }}">
@endpush
