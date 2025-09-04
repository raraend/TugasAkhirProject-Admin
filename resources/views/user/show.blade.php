@extends('layouts.app')

@section('title', 'Detail User')

@section('content')
<div class="container py-5">
    <div class="card shadow-sm rounded-4 p-4">
        <h2 class="text-center section-heading mb-4">
            <i class="bi bi-people-fill me-2"></i> Detail User
        </h2>

        {{-- Nama --}}
        <div class="info-box mb-3">
            <label class="info-title">Nama user</label>
            <div class="info-body">{{ $user->name_user }}</div>
        </div>

        {{-- Email --}}
        <div class="info-box mb-3">
            <label class="info-title">Email user</label>
            <div class="info-body">{{ $user->email }}</div>
        </div>

        {{-- Role dan Departemen berdampingan --}}
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="info-box">
                    <label class="info-title">Role user</label>
                    <div class="info-body">{{ $user->role->name_roles ?? '-' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <label class="info-title">Departemen user</label>
                    <div class="info-body">{{ $user->department->name_departments ?? '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Info Tambahan --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="info-box h-100">
                    <div class="info-title">Tanggal Dibuat</div>
                    <div class="info-body">
                        {{ $user->created_at ? $user->created_at->translatedFormat('d F Y, H:i') : '-' }}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box h-100">
                    <div class="info-title">Terakhir Diubah</div>
                    <div class="info-body">
                        {{ $user->updated_at ? $user->updated_at->translatedFormat('d F Y, H:i') : '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol Kembali --}}
        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('user.index') }}" class="btn btn-outline-dark px-4 py-2 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

{{-- Bootstrap Icons --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

{{-- Tambahan CSS --}}
<style>
    .section-heading {
        font-weight: 700;
        font-size: 24px;
        color: #1e1e1e;
    }

    .info-box {
        border-left: 4px solid rgba(20, 83, 45, 0.4);
        background-color: #ffffff;
        padding: 16px 20px;
        border-radius: 6px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }

    .info-title {
        font-size: 15px;
        font-weight: 600;
        color: #14532d;
        margin-bottom: 6px;
    }

    .info-body {
        font-size: 14.5px;
        font-weight: 500;
        color: rgb(0, 0, 0);
    }

    /* DARK MODE SUPPORT */
    .dark-mode .info-box {
        background-color: #1e1e1e !important;
        border-left-color: rgba(34, 197, 94, 0.6);
        box-shadow: 0 1px 4px rgba(255, 255, 255, 0.05);
    }

    .dark-mode .info-title {
        color: #a3e635 !important;
    }

    .dark-mode .info-body {
        background-color: #2c2c2c !important;
        color: #f0f0f0 !important;
    }

    .dark-mode .section-heading {
        color: #ffffff !important;
    }
</style>
@endsection
