<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header fw-semibold fs-5 rounded-top-4 text-center bg-body-secondary">
        <i class="bi bi-people-fill me-2"></i> Daftar User
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-borderless table-hover align-middle mb-0 small">
                <thead class="table-light text-secondary text-uppercase small fw-bold text-center">
                    <tr>
                        <th style="width: 6%;">No</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Departemen</th>
                        <th style="width: 22%;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-top" style="font-size: 0.95rem;">
                            <td class="text-muted text-center">{{ $loop->iteration }}</td>
                            <td class="fw-semibold text-start">{{ $user->name_user }}</td>
                            <td class="text-start text-muted text-capitalize">{{ $user->department->name_departments ?? '-' }}</td>
                            <td class="text-center">
                                <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
                                    <a href="{{ route('user.show', $user->uuid) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('user.edit', $user->uuid) }}" class="btn btn-sm btn-outline-warning rounded-circle" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('user.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus user ini?')" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4" style="font-size: 0.9rem;">
                                <i class="bi bi-info-circle me-1"></i> Tidak ada user ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
