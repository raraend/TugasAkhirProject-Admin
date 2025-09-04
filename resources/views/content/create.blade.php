@extends('layouts.app')
@php
    $user = auth()->user();
    $hideVisibilityOption = optional($user->department)->parent_id === 'D000';
@endphp

@section('content')
    <div class="container py-5">
        <div class="card shadow-sm rounded-4 p-4">
            <h2 class="text-center section-heading mb-4">Tambah Konten</h2>
            @if ($errors->has('duration'))
                <div class="alert alert-danger mb-4">
                    {{ $errors->first('duration') }}
                </div>
            @endif
            <form action="{{ route('content.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Judul --}}
                <div class="info-box mb-3">
                    <label class="info-title">Judul</label>
                    <input type="text" name="title" class="form-control info-body @error('title') is-invalid @enderror"
                        value="{{ old('title') }}">
                    <small class="text-muted d-block mt-1">Maksimal 50 karakter</small>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="info-box mb-3">
                    <label class="info-title">Deskripsi</label>
                    <textarea name="description" class="form-control info-body @error('description') is-invalid @enderror"
                        rows="4">{{ old('description') }}</textarea>
                    <small class="text-muted d-block mt-1">Maksimal 100 karakter</small>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                {{-- File --}}
                <div class="info-box mb-3">
                    <label class="info-title">File</label>
                    <input type="file" id="file_original" name="file_original"
                        class="form-control info-body @error('file_original') is-invalid @enderror">
                    <small class="form-text text-muted">
                        * Upload gambar/video dengan ukuran 4:5, 16:9, atau 9:16
                        <br>
                        * Ukuran maksimal file gambar 5MB, dan video 50MB
                        <br>
                    </small>

                    @error('file_original')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Durasi (hanya muncul kalau file = image) --}}
                <div class="info-box mb-3" id="duration-wrapper" style="display: none;">
                    <label class="info-title">Durasi Tayang (detik)</label>
                    <select name="duration" id="duration"
                        class="form-select info-body @error('duration') is-invalid @enderror">
                        <option value="" disabled selected>-- Pilih Durasi --</option>
                        <option value="5" {{ old('duration') == 5 ? 'selected' : '' }}>5 detik</option>
                        <option value="10" {{ old('duration') == 10 ? 'selected' : '' }}>10 detik</option>
                        <option value="20" {{ old('duration') == 20 ? 'selected' : '' }}>20 detik</option>
                        <option value="30" {{ old('duration') == 30 ? 'selected' : '' }}>30 detik</option>
                        <option value="60" {{ old('duration') == 60 ? 'selected' : '' }}>60 detik (1 menit)</option>
                        <option value="90" {{ old('duration') == 90 ? 'selected' : '' }}>90 detik (1.5 menit)</option>
                        <option value="120" {{ old('duration') == 120 ? 'selected' : '' }}>120 detik (2 menit)</option>
                        <option value="180" {{ old('duration') == 180 ? 'selected' : '' }}>180 detik (3 menit)</option>
                    </select>
                    @error('duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Boleh Dilihat Parent --}}
                @if (!$hideVisibilityOption)
                    {{-- tampilkan radio button --}}
                    <div class="info-box mb-4">
                        <label class="info-title d-block mb-2">Boleh Dilihat Departement Induk?</label>
                        <div class="d-flex gap-4">
                            <label>
                                <input type="radio" name="is_visible_to_parent" value="1" {{ old('is_visible_to_parent') == '1' ? 'checked' : '' }}>
                                Ya
                            </label>
                            <label>
                                <input type="radio" name="is_visible_to_parent" value="0" {{ old('is_visible_to_parent', '0') == '0' ? 'checked' : '' }}>
                                Tidak
                            </label>
                        </div>
                        @error('is_visible_to_parent')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    {{-- Jika anak langsung dari superadmin, otomatis isi 0 --}}
                    <input type="hidden" name="is_visible_to_parent" value="0">
                @endif


                {{-- Tanggal --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="info-box h-100">
                            <label class="info-title">Tanggal Mulai</label>
                            <input type="date" name="start_date"
                                class="form-control info-body @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box h-100">
                            <label class="info-title">Tanggal Selesai</label>
                            <input type="date" name="end_date"
                                class="form-control info-body @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Waktu --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="info-box h-100">
                            <label class="info-title">Jam Mulai</label>
                            <input type="time" name="start_time"
                                class="form-control info-body @error('start_time') is-invalid @enderror"
                                value="{{ old('start_time') }}">
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box h-100">
                            <label class="info-title">Jam Selesai</label>
                            <input type="time" name="end_time"
                                class="form-control info-body @error('end_time') is-invalid @enderror"
                                value="{{ old('end_time') }}">
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Repeat Hari --}}
                <div class="info-box mb-4">
                    <label class="info-title d-block mb-2">Hari Tayang</label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $num => $hari)
                            <label
                                class="day-box {{ is_array(old('repeat_days')) && in_array($num, old('repeat_days')) ? 'selected' : '' }}">
                                <input type="checkbox" name="repeat_days[]" value="{{ $num }}" {{ is_array(old('repeat_days')) && in_array($num, old('repeat_days')) ? 'checked' : '' }}>
                                {{ $hari }}
                            </label>
                        @endforeach
                    </div>
                    @error('repeat_days')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('content.index') }}" class="btn btn-outline-dark px-4 py-2 shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-success px-4 py-2 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Simpan
                    </button>
                </div>
            </form>
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

        .day-box {
            padding: 6px 12px;
            border-radius: 6px;
            background-color: #e4e7ea;
            color: #555;
            font-weight: 500;
            min-width: 80px;
            text-align: center;
        }

        .day-box.selected {
            background-color: #14532d;
            color: #fff;
            font-weight: 600;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #14532d;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            display: block;
        }
    </style>

    <script>
        document.getElementById('file_original').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const durationWrapper = document.getElementById('duration-wrapper');

            if (!file) {
                durationWrapper.style.display = 'none';
                return;
            }

            // Kalau IMAGE => tampilkan durasi
            if (file.type.startsWith('image/')) {
                durationWrapper.style.display = 'block';
            } else {
                // Kalau VIDEO => sembunyikan durasi dan reset
                durationWrapper.style.display = 'none';
                document.getElementById('duration').value = '';
            }
        });
    </script>

@endsection