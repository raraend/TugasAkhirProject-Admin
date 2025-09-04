{{-- Extend layout utama --}}
@extends('layouts.app')

{{-- Mulai section content --}}
@section('content')
<div class="container">
    {{-- Judul halaman edit departemen --}}
    <h1>Ubah Departemen</h1>
    {{-- Include form departemen dengan data department yang akan diedit dan daftar departments --}}
    @include('department._form', ['department' => $department, 'departments' => $departments])
</div>
@endsection
