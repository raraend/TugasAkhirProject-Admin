<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Konten Monitor')</title>
    {{-- Bootstrap + Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
@stack('styles')

    <style>
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
            background-color: #fefefe;
            background-image: linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.85)), url('https://www.transparenttextures.com/patterns/woven.png');
            background-repeat: repeat;
            background-size: auto;
            background-blend-mode: normal;
        }

        body.dark-mode {
            background-color: #121212 !important;
            color: #e0e0e0 !important;
            background-image: linear-gradient(rgba(18, 18, 18, 0.85), rgba(18, 18, 18, 0.85)), url('https://www.transparenttextures.com/patterns/woven.png');
            background-repeat: repeat;
            background-size: auto;
            background-blend-mode: normal;
        }

        .dark-mode .navbar,
        .dark-mode .card,
        .dark-mode .dropdown-menu,
        .dark-mode .form-control,
        .dark-mode .form-select,
        .dark-mode .bg-light,
        .dark-mode .bg-body,
        .dark-mode .bg-white,
        .dark-mode .bg-body-secondary {
            background-color: #1e1e1e !important;
            color: #ffffff !important;
            border-color: #333 !important;
        }

        .dark-mode .navbar .navbar-brand,
        .dark-mode .navbar .nav-link,
        .dark-mode .btn-theme-toggle,
        .dark-mode .form-label,
        .dark-mode .card-title,
        .dark-mode .dropdown-item {
            color: #f5f5f5 !important;
        }

        .dark-mode .form-control::placeholder,
        .dark-mode .form-select option {
            color: #ccc !important;
        }

        .dark-mode .text-dark,
        .dark-mode .section-heading,
        .dark-mode .info-title,
        .dark-mode .info-body {
            color: #f0f0f0 !important;
        }

        .dark-mode .text-muted {
            color: #d0d0d0 !important;
        }

        .dark-mode .btn-outline-dark {
            color: #fff;
            border-color: #bbb;
        }

        .dark-mode .btn-outline-dark:hover {
            background-color: #bbb;
            color: #000;
        }

        .dark-mode .day-box {
            background-color: #3a3a3a !important;
            color: #ccc !important;
        }

        .dark-mode .day-box.selected {
            background-color: #22c55e !important;
            color: #fff !important;
        }

        .dark-mode .info-box {
            background-color: #1e1e1e !important;
            border-left-color: rgba(34, 197, 94, 0.6);
            /* warna hijau terang */
            box-shadow: 0 1px 4px rgba(255, 255, 255, 0.05);
        }

        .dark-mode .info-title {
            color: #a3e635 !important;
            /* lime green */
        }

        .dark-mode .info-body {
            background-color: #2c2c2c !important;
            color: #f0f0f0 !important;
        }

        .dark-mode .form-control {
            background-color: #2a2a2a;
            color: #e0e0e0;
            border-color: #444;
        }

        .dark-mode .form-control:focus {
            border-color: #4ade80;
            box-shadow: 0 0 0 0.1rem rgba(74, 222, 128, 0.25);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 0.5px;
        }

        .nav-item .nav-link {
            font-weight: 500;
        }

        .btn-theme-toggle {
            border: none;
            background: none;
            font-size: 1rem;
            padding: 0.5rem 0.8rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-theme-toggle:hover {
            color: #0d6efd;
        }

        .dropdown-menu {
            border-radius: 0.5rem;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .theme-option:hover {
            background-color: #f8f9fa;
        }

        .dark-mode .theme-option:hover {
            background-color: #2c2c2c;
        }

        .btn-logout {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .btn-logout:hover {
            background-color: #c82333;
            color: white;
        }

        footer {
            font-size: 0.9rem;
            text-align: center;
            padding: 1rem;
            color: #888;
        }

        .dark-mode footer {
            color: #aaa;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
            /* Membuat main mengisi ruang sisa vertikal */
        }




        /* Dark Mode - Tabel Umum */
        .dark-mode .table {
            background-color: #1e1e1e !important;
            color: #f0f0f0 !important;
        }

        /* Header Tabel */
        .dark-mode .table thead {
            background-color: #2c2c2c !important;
            color: #ffffff !important;
        }

        /* Sel tabel */
        .dark-mode .table td,
        .dark-mode .table th {
            background-color: #1e1e1e !important;
            color: #f0f0f0 !important;
            border-color: #333 !important;
        }



        /* Badge di dalam tabel */
        .dark-mode .badge.bg-secondary-subtle {
            background-color: #3a3a3a !important;
            color: #e0e0e0 !important;
            border: 1px solid rgba(236, 236, 236, 0.47);
        }

        /* Opsional: hover di sel individual */
        .dark-mode .table-hover tbody td:hover {
            background-color: rgb(71, 71, 71) !important;
            color: #ffffff !important;
        }

        .table-hover tbody td {
            transition: background-color 0.2s ease, color 0.2s ease;
        }
    </style>
    
</head>

<body class="bg-light text-dark">
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm px-4 py-2">
        <a class="navbar-brand" href="#">Konten Monitor</a>

        <div class="ms-auto d-flex align-items-center gap-3">
            {{-- Beranda --}}
            @php
                $roleId = auth()->user()->role->id_roles ?? null;
            @endphp

            @if($roleId === 'RL01')
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link">Beranda</a>
            @elseif($roleId === 'RL02')
                <a href="{{ route('admin.dashboard') }}" class="nav-link">Beranda</a>
            @endif

            {{-- Tema Dropdown --}}
            <div class="dropdown">
                <button class="btn-theme-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-palette"></i> Tema
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><button class="dropdown-item theme-option" onclick="setTheme('light')">Terang</button></li>
                    <li><button class="dropdown-item theme-option" onclick="setTheme('dark')">Gelap</button></li>
                </ul>
            </div>

            {{-- Tombol Keluar --}}
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="button" class="btn btn-sm btn-logout" onclick="confirmLogout()">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
            </form>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="container-fluid px-4 py-4">
        @yield('content')
        @stack('scripts')
        @stack('styles')
    </main>

    {{-- Footer --}}
    <footer>
        &copy; {{ now()->year }} Rara Eva Maharani - S1 Thesis TI UMY
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Theme Script --}}
    <script>
        function setTheme(mode) {
            const isDark = mode === 'dark';
            document.body.classList.toggle('dark-mode', isDark);
            localStorage.setItem('theme', mode);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);
        });

        //NBUAT LOG OT
        function confirmLogout() {
    if (confirm('Apakah kamu yakin ingin logout?')) {
        document.getElementById('logout-form').submit();
    }
}
    </script>


</body>

</html>