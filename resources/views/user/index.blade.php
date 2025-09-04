@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')
    <div class="container py-4">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @elseif(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Header: Tombol Tambah & Pencarian --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="{{ route('user.create') }}" class="btn btn-primary">Tambah User</a>
 
            <div>
                <input type="text" id="search-input" name="search" class="form-control form-control-sm"
                    placeholder="Cari user...">
            </div>
        </div>

        {{-- Tabel User --}}
        <div id="user-table">
            @include('user.partials.table', ['users' => $users])
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        let searchDelay;

        $('#search-input').on('input', function () {
            clearTimeout(searchDelay);

            searchDelay = setTimeout(() => {
                const search = $('#search-input').val();

                $.ajax({
                    url: "{{ route('user.index') }}",
                    method: 'GET',
                    data: { search, _: new Date().getTime() }, // `_` untuk mencegah cache
                    success: function (data) {
                        $('#user-table').html(data);
                    },
                    error: function () {
                        alert('Gagal memuat data user.');
                    }
                });
            }, 300);
        });
    </script>
@endpush
