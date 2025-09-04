{{-- Extend layout utama --}}
@extends('layouts.app')

{{-- Mulai section content --}}
@section('content')
<div class="container">
    {{-- Judul halaman tambah departemen --}}
    <h1>Tambah Departemen</h1>
    {{-- Include form departemen dan kirim data departments --}}
    @include('department._form', ['departments' => $departments])
</div>
@endsection
