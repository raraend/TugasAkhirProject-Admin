@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="container py-5">
    <div class="card shadow-sm rounded-4 p-4">
        <h2 class="text-center section-heading mb-4 text-success">
            <i class="bi bi-person-plus-fill me-2"></i> Tambah User
        </h2>

        <form action="{{ route('user.store') }}" method="POST">
            @csrf

            {{-- Nama User --}}
            <div class="info-box mb-3">
                <label class="info-title">Nama user</label>
                <input type="text" name="name_user"
                       class="form-control info-body @error('name_user') is-invalid @enderror"
                       value="{{ old('name_user') }}" maxlength="50">
                <small class="form-text text-muted">
                    Gunakan format standar: <strong>[Admin] + [Jenis Unit] + [Nama user]</strong>
                </small>
                @error('name_user')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email User --}}
            <div class="info-box mb-3">
                <label class="info-title">Email user</label>
                <input type="email" name="email"
                       class="form-control info-body @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" maxlength="50">
                <small class="form-text text-muted">
                    Email harus menggunakan domain <strong>@gmail.com</strong>
                </small>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="info-box mb-3">
                <label class="info-title">Password</label>
                <input type="password" name="password"
                       class="form-control info-body @error('password') is-invalid @enderror">
                <small class="form-text text-muted">Minimal <strong>8 karakter</strong></small>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Role --}}
            <div class="info-box mb-3">
                <label class="info-title">Role user</label>
                <select name="role_id" class="form-select info-body @error('role_id') is-invalid @enderror">
                    <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id_roles }}"
                            {{ old('role_id') == $role->id_roles ? 'selected' : '' }}>
                            {{ $role->name_roles }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Departemen --}}
            <div class="info-box mb-3">
                <label class="info-title">Departemen yang dikelola</label>
                <select name="id_departments"
                        class="form-select info-body @error('id_departments') is-invalid @enderror"
                        style="max-height:200px; overflow-y:auto;">
                    <option value="" disabled {{ old('id_departments') ? '' : 'selected' }}>Pilih Departemen</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id_departments }}"
                            {{ old('id_departments') == $department->id_departments ? 'selected' : '' }}>
                            {{ $department->name_departments }}
                        </option>
                    @endforeach
                </select>
                @error('id_departments')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="d-flex justify-content-end gap-3 mt-4">
                <a href="{{ route('user.index') }}" class="btn btn-outline-dark px-4 py-2 shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Batal
                </a>
                <button type="submit" class="btn btn-success px-4 py-2 shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Tambah User
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Bootstrap Icons --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

{{-- Tambahan CSS --}}
<style>
.section-heading { font-weight: 700; font-size: 24px; color: #14532d; }
.info-box { border-left: 4px solid rgba(20, 83, 45, 0.4); background-color: #ffffff; padding: 16px 20px; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.info-title { font-size: 15px; font-weight: 600; color: #14532d; margin-bottom: 6px; }
.info-body { font-size: 14.5px; font-weight: 500; color: #000; }
.form-control:focus, .form-select:focus { box-shadow: none; border-color: #14532d; }
.btn-success { background-color: rgb(25,135,84); color:white; }
.btn-success:hover { background-color: rgb(19,184,107); }
.btn-outline-dark:hover { background-color: #222; color:white; }
</style>
@endsection
