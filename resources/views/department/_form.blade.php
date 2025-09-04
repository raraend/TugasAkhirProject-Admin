    @extends('layouts.app')

    @section('content')
    <div class="container py-4">
        <h2 class="text-center section-heading mb-4 text-success">
            {{ isset($department) ? 'Edit Departemen' : 'Tambah Departemen' }}
        </h2>

        @if ($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow rounded-4">
            <div class="card-body px-4 py-4">
                <form method="POST"
                    action="{{ isset($department) ? route('department.update', $department->id_departments) : route('department.store') }}">
                    @csrf
                    @if(isset($department))
                        @method('PUT')
                    @endif

                    {{-- Nama Departemen --}}
                    <div class="info-box mb-3">
                        <label for="name_departments" class="info-title">Nama Departemen</label>
                        <input type="text" name="name_departments" id="name_departments"
                            class="form-control info-body @error('name_departments') is-invalid @enderror"
                            value="{{ old('name_departments', $department->name_departments ?? '') }}"
                            required>
                        @error('name_departments')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Gunakan format standar institusi: [Jenis Unit] + [Nama Unit]</small>
                        <br>
                        <small class="text-muted">Sebagai contoh, Fakultas A, Prodi A, Direktorat A <small>

                    </div>

                    {{-- Parent Departemen --}}
                    <div class="info-box mb-3">
                        <label for="parent_id" class="info-title">Departemen Induk</label>
                        <select name="parent_id" id="parent_id" 
                            class="form-select info-body @error('parent_id') is-invalid @enderror">
                            <option value="">-- Tidak ada induk departemen --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id_departments }}"
                                    @if (isset($department) && $department->parent_id == $dept->id_departments) selected @endif>
                                    {{ $dept->name_departments }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <a href="{{ route('department.index') }}" class="btn btn-outline-dark px-4 py-2 shadow-sm">
                            <i class="bi bi-arrow-left me-1"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-success px-4 py-2 shadow-sm">
                            <i class="bi bi-check-circle me-1"></i>
                            {{ isset($department) ? 'Ubah Departemen' : 'Simpan Departemen' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Style Form --}}
    <style>
        .section-heading {
            font-weight: 700;
            font-size: 24px;
            color: #14532d;
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
            color: #000;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.15rem rgba(25, 135, 84, 0.25);
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            display: block;
        }

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
    </style>
    @endsection
