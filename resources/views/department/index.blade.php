@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

   {{-- Tombol Tambah dan Live Search --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('department.create') }}" class="btn btn-success btn-sm shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Departemen
        </a>
    </div>

    <div class="d-flex justify-content-end">
        <input type="text" id="search-input" name="search" class="form-control w-auto" placeholder="Cari departemen...">
    </div>
</div>



    {{-- Tabel Departemen --}}
    <div id="department-table">
        @include('department.partials.table', ['departments' => $departments])
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const reloadDepartments = () => {
        const search = $('#search-input').val();

        $.ajax({
            url: "{{ route('department.index') }}",
            method: "GET",
            data: {
                search: search
            },
            success: function(data) {
                $('#department-table').html(data);
            },
            error: function() {
                alert('Gagal memuat data departemen.');
            }
        });
    };

    let searchDelay;
    $('#search-input').on('input', function () {
        clearTimeout(searchDelay);
        searchDelay = setTimeout(() => {
            reloadDepartments();
        }, 300);
    });
</script>
@endpush
