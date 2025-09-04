@extends('layouts.app') {{-- extend layout utama --}}

@section('content')
<div class="container mt-4">
    {{-- Judul halaman --}}
    <h1 class="text-2xl font-bold mb-4">Detail Departemen</h1>

    {{-- Data detail departemen --}}
    <p><strong>ID:</strong> {{ $department->id_departments }}</p> {{-- tampilkan ID --}}
    <p><strong>Nama:</strong> {{ $department->name_departments }}</p> {{-- tampilkan nama --}}
    <p><strong>Parent:</strong> 
        {{ $department->parent ? $department->parent->name_departments : '-' }} {{-- tampilkan nama parent kalau ada --}}
    </p>

    {{-- Tombol kembali ke daftar --}}
    <a href="{{ route('department.index') }}" 
       class="bg-blue-500 text-white px-4 py-2 mt-4 inline-block rounded">
        Kembali
    </a>
</div>
@endsection
