@forelse ($contentsToShow as $content)
    @php
        // URL file konten dengan versi timestamp (supaya cache browser ikut refresh kalau file di-update)
        $fileUrl = route('content.serve.file', $content->uuid) . '?v=' . \Carbon\Carbon::parse($content->updated_at)->timestamp;

        // Ekstensi file (untuk menentukan apakah gambar / video / lainnya)
        $ext = strtolower(pathinfo($content->file_original, PATHINFO_EXTENSION));

        // Cek apakah konten ini milik department user sendiri
        $isOwn = $content->departments->pluck('id_departments')->contains(auth()->user()->id_departments);

        // Ambil child department dari filter yang sedang aktif
        $childDept = $departments->firstWhere('uuid', request('department_filter'));

        // Ambil relasi pivot jika ada (untuk akses status is_tayang_request dan is_visible_to_parent)
        $pivot = $childDept
            ? $content->departments->firstWhere('id_departments', $childDept->id_departments)?->pivot
            : null;
    @endphp

    <!-- Kartu konten -->
    <div class="position-relative content-card-hover flex-shrink-0" style="min-width: 240px;" data-id="{{ $content->id }}">
        <div class="card h-100 border-0 shadow-sm">
            <div class="position-relative">

                {{-- Tampilan file berdasarkan tipe konten --}}
                @if (in_array($ext, ['jpg', 'jpeg', 'png']))
                    <!-- Gambar -->
                    <img src="{{ $fileUrl }}" class="card-img-top vertical-img" alt="Gambar Konten">
                @elseif (in_array($ext, ['mp4', 'webm']))
                    <!-- Video -->
                    <video src="{{ $fileUrl }}" controls class="w-100 vertical-img"></video>
                @else
                    <!-- Jika bukan gambar/video -->
                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center"
                        style="height: 320px;">Tidak Ada Gambar</div>
                @endif

                <!-- Overlay di atas gambar/video (status + aksi) -->
                <div class="overlay-action text-center">
                    <!-- Badge status konten -->
                    <span class="badge 
                        @if ($pivot)
                            {{ $pivot->is_tayang_request ? 'bg-success' : 'bg-danger' }}
                        @else
                            {{ $content->status === 'Sedang Tayang' ? 'bg-success' : ($content->status === 'Akan Tayang' ? 'bg-warning text-dark' : 'bg-danger') }}
                        @endif
                        mb-2">

                        {{-- Teks status --}}
                        @if ($pivot)
                            {{ $pivot->is_tayang_request ? 'Sedang Tayang' : 'Sedang Tidak Tayang' }}
                        @else
                            {{ $content->status }}
                        @endif
                    </span><br>

                    <!-- Info tanggal & jam tayang -->
                    <div class="text-white small mb-2">
                        <div><strong>Tanggal:</strong>
                            {{ \Carbon\Carbon::parse($content->start_date)->format('d M') }} –
                            {{ $content->end_date ? \Carbon\Carbon::parse($content->end_date)->format('d M Y') : '-' }}
                        </div>
                        <div><strong>Waktu:</strong>
                            {{ \Carbon\Carbon::parse($content->start_time)->format('H:i') }} –
                            {{ $content->end_time ? \Carbon\Carbon::parse($content->end_time)->format('H:i') : '-' }}
                        </div>
                    </div>

                    <!-- Tombol aksi -->
                    @if ($isOwn)
                        <!-- Jika konten milik department user sendiri -->
                        <a href="{{ route('content.show', $content->uuid) }}?status_filter={{ request('status_filter') }}&department_filter={{ request('department_filter') }}"
                            class="btn btn-sm btn-outline-info">Lihat</a>
                        <a href="{{ route('content.edit', $content->uuid) }}?status_filter={{ request('status_filter') }}&department_filter={{ request('department_filter') }}"
                            class="btn btn-sm btn-outline-primary">Ubah</a>
                        <form
                            action="{{ route('content.destroy', $content->id) }}?status_filter={{ request('status_filter') }}&department_filter={{ request('department_filter') }}"
                            method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                        </form>
                    @elseif ($pivot)
                        <!-- Jika konten milik child department -->
                        <form action="{{ $pivot->is_tayang_request
                            ? route('content.cancelTayang', $content->id)
                            : route('content.requestTayang', $content->id)
                        }}?department_filter={{ request('department_filter') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="btn btn-sm {{ $pivot->is_tayang_request ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                <i class="bi {{ $pivot->is_tayang_request ? 'bi-x-circle' : 'bi-eye' }}"></i>
                                {{ $pivot->is_tayang_request ? 'Batal Tayang' : 'Tayangkan' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Judul konten -->
            <div class="card-body">
                <h5 class="card-title text-center">{{ $content->title }}</h5>
            </div>
        </div>
    </div>
@empty
    <!-- Jika tidak ada konten -->
    <div class="text-muted">Tidak ada konten ditemukan.</div>
@endforelse
