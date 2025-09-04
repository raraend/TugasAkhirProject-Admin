<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Monitor</title>
    {{-- Memuat CSS Bootstrap dari CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Mengatur latar belakang gradient dan layout tengah vertikal dan horizontal */
        body {
            background: linear-gradient(to right, rgb(0, 0, 0), rgb(38, 88, 66));
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
            overflow: hidden;
        }

        /* Styling kartu login */
        .login-card {
            background-color: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
            opacity: 0; /* awalnya transparan */
            transform: translateY(30px); /* posisi awal di bawah */
            animation: fadeInUp 0.8s ease forwards; /* animasi masuk naik */
        }

        /* Animasi fadeInUp */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Judul login */
        .login-title {
            font-size: 1.75rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1.5rem;
            color: rgb(18, 130, 61);
        }

        /* Fokus pada input form agar border berubah warna */
        .form-control:focus {
            box-shadow: none;
            border-color: rgb(2, 31, 93);
        }

        /* Tombol login berwarna hijau */
        .btn-primary {
            background-color: rgb(18, 130, 61);
            border: none;
        }

        /* Warna tombol saat hover */
        .btn-primary:hover {
            background-color: rgb(73, 176, 113);
        }
    </style>
</head>
<body>

    {{-- Kartu login --}}
    <div class="login-card">
        {{-- Judul --}}
        <div class="login-title">Masuk ke Sistem</div>

        {{-- Menampilkan pesan error jika ada (contoh: login gagal) --}}
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Form login dengan metode POST ke URL /login --}}
        <form method="POST" action="{{ url('login') }}">
            @csrf {{-- Token CSRF untuk keamanan form --}}

            {{-- Input email --}}
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email" 
                    name="email" 
                    required 
                    value="{{ old('email') }}">
                {{-- Menampilkan error validasi untuk email --}}
                @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Input password --}}
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password" 
                    required>
                {{-- Menampilkan error validasi untuk password --}}
                @error('password')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tombol submit login --}}
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

</body>
</html>
