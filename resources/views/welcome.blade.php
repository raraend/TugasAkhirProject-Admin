<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #000000, #265842);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
            overflow: hidden;
            color: #ffffff;
        }


        .splash-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 1rem;
        }

        h1,
        p {
            color: #ffffff;
            /* pastikan header & paragraf putih */
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        @media (max-width: 576px) {
            h1 {
                font-size: 1.5rem;
            }

            .spinner-border {
                width: 2rem;
                height: 2rem;
            }
        }

        .spinner-border.custom-green {
            border-top-color: #22c55e;
            /* hijau */
            border-right-color: rgba(255, 255, 255, 0.2);
            border-bottom-color: rgba(255, 255, 255, 0.2);
            border-left-color: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body>
    <div class="splash-container">
        <h1>Sistem Penjadwalan Konten</h1>
        <p>Mengalihkan ke halaman login...</p>
        <div class="spinner-border text-primary custom-green" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script>
        setTimeout(function () {
            window.location.href = "{{ route('login') }}";
        }, 2000); // 2 detik redirect
    </script>
</body>

</html>