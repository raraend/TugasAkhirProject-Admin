@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="card shadow-sm rounded-4 p-4">
            <h2 class="text-center section-heading mb-4">Edit Informasi Konten</h2>

            <form action="{{ route('content.update', $content->uuid) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Judul --}}
                <div class="info-box mb-3">
                    <label class="info-title">Judul</label>
                    <input type="text" name="title" class="form-control info-body @error('title') is-invalid @enderror"
                        value="{{ old('title', $content->title) }}">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div class="info-box mb-3">
                    <label class="info-title">Deskripsi</label>
                    <textarea name="description" class="form-control info-body @error('description') is-invalid @enderror"
                        rows="4">{{ old('description', $content->description) }}</textarea>
                    <small class="text-muted d-block mt-1">Maksimal 200 kata</small>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- File --}}
                <div class="info-box mb-3">
                    <label class="info-title">File</label>
                    <input type="file" name="file_original" id="fileInput"
                        class="form-control info-body @error('file_original') is-invalid @enderror">
                    <small class="form-text text-muted">
                        * Upload gambar dengan ukuran persegi 4:5, horizontal 16:9 atau potret 9:16
                        <br>
                           * Ukuran maksimal file gambar 5MB, dan video 50MB
                    </small>
                    @if ($content->file_original)
                        <p class="mt-2 mb-0">
                            <small>File saat ini: <strong>{{ $content->file_original }}</strong></small>
                        </p>
                    @endif
                    @error('file_original')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                {{-- Durasi --}}
                <div class="info-box mb-3" id="durationBox">
                    <label class="info-title">Durasi Tayang (detik)</label>
                    <select name="duration" class="form-select info-body @error('duration') is-invalid @enderror"
                        id="durationSelect">
                        <option value="" disabled selected>-- Pilih Durasi --</option>
                        @foreach([5, 10, 20, 30, 60, 90, 120, 180] as $d)
                            <option value="{{ $d }}" {{ old('duration', $content->duration) == $d ? 'selected' : '' }}>
                                {{ $d }} detik
                                {{ $d == 60 ? '(1 menit)' : ($d == 90 ? '(1.5 menit)' : ($d == 120 ? '(2 menit)' : ($d == 180 ? '(3 menit)' : ''))) }}
                            </option>
                        @endforeach
                    </select>
                    @error('duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                {{-- Boleh Dilihat Parent --}}
                <div class="info-box mb-4">
                    <label class="info-title d-block mb-2">Boleh Dilihat Departement Induk?</label>
                    <div class="d-flex gap-4">
                        @php
                            $pivotValue = optional($content->departments->firstWhere('id_departments', auth()->user()->id_departments))->pivot->is_visible_to_parent;
                        @endphp
                        <label>
                            <input type="radio" name="is_visible_to_parent" value="1" {{ old('is_visible_to_parent', $pivotValue) == 1 ? 'checked' : '' }}> Ya
                        </label>
                        <label>
                            <input type="radio" name="is_visible_to_parent" value="0" {{ old('is_visible_to_parent', $pivotValue) == 0 ? 'checked' : '' }}> Tidak
                        </label>
                    </div>
                    @error('is_visible_to_parent')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tanggal --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="info-box h-100">
                            <label class="info-title">Tanggal Mulai</label>
                            <input type="date" name="start_date"
                                class="form-control info-body @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date', $content->start_date) }}">
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
                                value="{{ old('end_date', $content->end_date) }}">
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
                                value="{{ old('start_time', \Carbon\Carbon::parse($content->start_time)->format('H:i')) }}">
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
                                value="{{ old('end_time', $content->end_time ? \Carbon\Carbon::parse($content->end_time)->format('H:i') : '') }}">
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Repeat Hari --}}
                <div class="info-box mb-4">
                    <label class="info-title d-block mb-2">Hari Tayang</label>
                    @php $selectedDays = old('repeat_days', explode(',', $content->repeat_days)); @endphp
                    <div class="d-flex flex-wrap gap-2">
                        @foreach([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $num => $hari)
                            <label class="day-box {{ in_array((string) $num, $selectedDays) ? 'selected' : '' }}">
                                <input type="checkbox" name="repeat_days[]" value="{{ $num }}" @checked(in_array((string) $num, $selectedDays))> {{ $hari }}
                            </label>
                        @endforeach
                    </div>
                    @error('repeat_days')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('content.index', [
        'status_filter' => request('status_filter', 'sedang'),
        'department_filter' => request('department_filter', 'self'),
    ]) }}" class="btn btn-outline-dark px-4 py-2 shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-success px-4 py-2 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Edit
                    </button>
                </div>

                {{-- Hidden Filter --}}
                <input type="hidden" name="status_filter" value="{{ request('status_filter') }}">
                <input type="hidden" name="department_filter" value="{{ request('department_filter') }}">
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

        /* DARK MODE */
        body.dark-mode .info-box {
            color: #e0e0e0 !important;
            border-left-color: rgba(34, 197, 94, 0.4);
        }

        body.dark-mode .info-title {
            color: #86efac !important;
        }

        body.dark-mode .info-body {
            color: #e5e5e5 !important;
        }

        body.dark-mode .day-box {
            background-color: #333 !important;
            color: #ccc !important;
        }

        body.dark-mode .day-box.selected {
            background-color: #22c55e !important;
            color: #fff !important;
        }

        body.dark-mode .section-heading {
            color: #ffffff !important;
        }
    </style>

    {{-- JS untuk toggle durasi --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('fileInput');
            const durationBox = document.getElementById('durationBox');

            function toggleDuration() {
                const file = fileInput.files[0];
                if (!file) {
                    // Tidak ganti file, tetap tampilkan durasi jika image lama
                    const oldFileName = "{{ $content->file_original }}";
                    const oldIsImage = oldFileName.match(/\.(jpg|jpeg|png)$/i);
                    durationBox.style.display = oldIsImage ? 'block' : 'none';
                    return;
                }
                const isImage = file.type.startsWith('image/');
                durationBox.style.display = isImage ? 'block' : 'none';
            }

            fileInput.addEventListener('change', toggleDuration);
            toggleDuration(); // cek awal page load
        });
    </script>
@endsection