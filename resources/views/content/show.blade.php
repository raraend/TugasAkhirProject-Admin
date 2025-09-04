@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="card shadow-sm rounded-4 p-4">
            <h2 class="text-center section-heading mb-4">Detail Informasi Konten</h2>

            {{-- File URL & Extension --}}
           @php
    $fileUrl = route('content.serve.file', $content->uuid);
    $ext = strtolower(pathinfo($content->file_original, PATHINFO_EXTENSION));
@endphp

            {{-- Preview Media --}} 
            <div class="mb-4 text-center">
                @if ($content->file_original)
                    @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                        <img src="{{  route('content.serve.file', $content->uuid) }}" alt="Gambar Konten" class="img-fluid rounded shadow-sm"
                            style="max-height: 300px; object-fit: contain;">
                    @elseif (in_array($ext, ['mp4', 'avi', 'mov', 'webm']))
                        <video controls class="rounded shadow-sm mb-2"
                            style="width: 100%; max-width: 700px; max-height: 300px; object-fit: contain;">
                            <source src="{{ $fileUrl }}" type="video/{{ $ext }}">
                            Browser tidak mendukung video.
                        </video>
                    @else
                        <div class="alert alert-info">File terlampir: {{ $content->file_original }}</div>
                    @endif
                @else
                    <div class="alert alert-warning">Tidak ada file untuk konten ini.</div>
                @endif
                <p class="mb-2 text-muted">
                    <small>File saat ini: <strong>{{ $content->file_original }}</strong></small>
                </p>
            </div>

            {{-- Judul --}}
            <div class="info-box mb-3">
                <label class="info-title">Judul</label>
                <div class="info-body">{{ $content->title }}</div>
            </div>

            {{-- Deskripsi --}}
            <div class="info-box mb-3">
                <label class="info-title">Deskripsi</label>
                <div class="info-body">{{ $content->description }}</div>
            </div>

            {{-- Durasi --}}
            <div class="info-box mb-3">
                <label class="info-title">Durasi Tayang (detik)</label>
                <div class="info-body">{{ $content->duration }} Detik</div>
            </div>

            {{-- Boleh Dilihat Parent --}}
            <div class="info-box mb-3">
                <label class="info-title">Boleh Dilihat Departement Induk?</label>
                @php
                    $visibility = optional($content->departments->firstWhere('id_departments', auth()->user()->id_departments))->pivot->is_visible_to_parent;
                @endphp
                <div class="info-body fw-bold {{ $visibility ? 'text-success' : 'text-danger' }}">
                    {{ $visibility ? 'Ya' : 'Tidak' }}
                </div>
            </div>

            {{-- Tanggal & Jam Tayang --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="info-box h-100">
                        <div class="info-title">Tanggal Tayang</div>
                        <div class="info-body">
                            {{ \Carbon\Carbon::parse($content->start_date)->translatedFormat('d F Y') }}
                            @if($content->end_date)
                                - {{ \Carbon\Carbon::parse($content->end_date)->translatedFormat('d F Y') }}
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box h-100">
                        <div class="info-title">Waktu Tayang</div>
                        <div class="info-body">
                            {{ \Carbon\Carbon::parse($content->start_time)->format('H:i') }}
                            @if($content->end_time)
                                - {{ \Carbon\Carbon::parse($content->end_time)->format('H:i') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Repeat Hari --}}
            <div class="info-box mb-4">
                <label class="info-title d-block mb-2">Hari Tayang</label>
                @php
                    $selectedDays = explode(',', $content->repeat_days);
                    $hariList = [
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu'
                    ];
                @endphp
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($hariList as $num => $hari)
                        <div class="day-box {{ in_array((string) $num, $selectedDays) ? 'selected' : '' }}">
                            {{ $hari }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Info Tambahan --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="info-box h-100">
                        <div class="info-title">Informasi Tambahan</div>
                        <div class="info-body">
                            <p><strong>Dibuat oleh:</strong> {{ $content->creator->name_user ?? '-' }}</p>
                            <p><strong>Diubah oleh:</strong> {{ $content->modifier->name_user ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Kembali --}}
            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('content.index', [
                    'status_filter' => request('status_filter', 'sedang'),
                    'department_filter' => request('department_filter', 'self'),
                ]) }}" class="btn btn-outline-dark px-4 py-2 shadow-sm">
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

        DARK MODE (bisa aktifkan jika pakai dark mode) */
        
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
@endsection
