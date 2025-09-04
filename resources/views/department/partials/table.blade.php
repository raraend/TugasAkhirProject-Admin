<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header fw-semibold fs-5 rounded-top-4 text-center bg-body-secondary">
        <i class="bi bi-diagram-3 me-2"></i> Daftar Departemen
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-borderless table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase small fw-bold text-center">
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th class="text-center">Nama Departemen</th>
                        <th>Induk Departemen</th>
                        <th style="width: 22%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr class="border-top" style="font-size: 0.95rem;">
                            <td class="text-muted text-start">{{ $department->id_departments }}</td>
                            <td class="fw-semibold text-start">{{ $department->name_departments }}</td>
                            <td class="text-start">
                                <span class="badge bg-secondary-subtle text-dark text-capitalize dark-mode-badge">
                                    {{ $department->parent ? $department->parent->name_departments : '–' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('department.edit', $department->uuid) }}"
                                        class="btn btn-sm btn-outline-warning rounded-circle" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Form Hapus --}}
                                    <form action="{{ route('department.destroy', $department->id_departments) }}"
                                          method="POST" onsubmit="return confirm('Yakin ingin menghapus departemen ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <div class="d-flex align-items-center gap-2">
                                            {{-- Select Aksi --}}
                                            <select name="deleteAction" class="form-select form-select-sm shadow-sm" style="width: 160px;" required>
                                                <option value="" disabled selected>Pilih Aksi...</option>
                                                <option value="delete">Hapus Semua</option>
                                                <option value="deleteAndMove">Hapus & Pindahkan</option>
                                            </select>

                                            {{-- Tombol Submit --}}
                                            <button type="submit" class="btn btn-sm btn-danger rounded shadow-sm" title="Hapus Departemen">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4" style="font-size: 0.9rem;">
                                <i class="bi bi-info-circle me-1"></i> Tidak ada departemen ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
