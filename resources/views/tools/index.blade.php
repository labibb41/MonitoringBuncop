<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alat Lainnya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/vitaroot.css') }}">
    <style>
        .tools-wrap {
            background: #fff;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 12px 28px rgba(45, 90, 39, 0.12);
            border: 2px solid #e8f5e9;
            max-width: 720px;
            margin: 40px auto;
        }
        .tools-title {
            font-size: 2rem;
            margin-bottom: 12px;
            color: #2d5a27;
        }
        .tools-text {
            font-size: 1rem;
            color: #4b6b4a;
            margin-bottom: 20px;
        }
        .tools-back {
            display: inline-block;
            background: #4caf50;
            color: #fff;
            padding: 10px 16px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="tools-wrap">
            <h1 class="tools-title">Alat Lainnya</h1>
            <p class="tools-text">Alat yang baru akan ada di halaman ini. Silakan kembali lagi untuk pembaruan berikutnya.</p>
            <a class="tools-back" href="{{ route('home') }}">Kembali ke VitaRoot</a>
        </div>
    </div>
</body>
</html>
