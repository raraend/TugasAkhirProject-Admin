@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="container mt-5">

    
        {{-- ini hero Box --}}
        <div class="p-5 mb-5 rounded-4 shadow-lg dashboard-hero text-center">
            <h1 class="display-4 fw-bold mb-3">
                <i class="bi bi-speedometer2 me-2 "></i>Dashboard Admin
            </h1>
            <p class="fs-5">
                Selamat {{ now()->format('H') < 12 ? 'pagi' : (now()->format('H') < 18 ? 'siang' : 'malam') }},
                <strong>{{ Auth::user()->name_user }}</strong> 👋<br>
                Ayo kelola konten departemenmu dengan mudah dan cepat.
            </p>
        </div>

        <div class="flying-leaves-bg"></div>


        {{-- Konten Card --}}
        <div class="d-flex justify-content-center">
            <div class="card dashboard-card text-center shadow-sm border-0 rounded-4 p-4">
                <h5 class="card-title fw-semibold">Manajemen Konten</h5>
                <p class="card-text">Tambah, ubah, dan hapus konten departemen Anda dengan mudah.</p>
                <a href="{{ route('content.index') }}" class="btn btn-success mt-2 shadow-sm px-3 py-1"
                    style="font-size: 0.9rem;">
                    <i class="bi bi-arrow-right-circle me-1"></i> Kelola Konten
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')

    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endpush