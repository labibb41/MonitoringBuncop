<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vita Root - Integrated Smart Farming</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/vitaroot.css') }}">
</head>
<body>
    <div class="container">
        <div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background:#111827; color:#fff; border:0; padding:8px 14px; border-radius:8px; cursor:pointer;">
                    Logout
                </button>
            </form>
        </div>
        <!-- Brand -->
        <div class="brand">
             <img src="{{ asset('images/vita root.png') }}" alt="Vitaroot" class="logo-image">
            <h1 class="brand-name">Vita Root</h1>
            <p class="brand-tagline">Integrated Smart Farming Control</p>
        </div>

        <!-- Partner Logos (sesuaikan dengan gambar yang ada) -->
        <div class="partners">
            <div class="partner-item">
                <img src="{{ asset('images/petrokimia.png') }}" alt="Petrokimia Gresik" class="partner-logo">
            </div>
            <div class="partner-item">
                <img src="{{ asset('images/bumn.png') }}" alt="BUMN" class="partner-logo">
            </div>
            <div class="partner-item">
                <img src="{{ asset('images/danantara.png') }}" alt="Danantara" class="partner-logo">
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <a href="{{ route('incubator') }}" class="card">
                <div class="card-icon">
                    <i class="fas fa-egg"></i>
                </div>
                <h2 class="card-title">Inkubator Tanaman</h2>
                <p class="card-description">Kontrol suhu, kelembaban, dan pencahayaan untuk pertumbuhan optimal tanaman.</p>
                <span class="card-link">Buka Dashboard <i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="{{ route('nutrimix') }}" class="card">
                <div class="card-icon">
                    <i class="fas fa-microchip"></i>
                </div>
                <h2 class="card-title">Nutrimix Controller</h2>
                <p class="card-description">Kontrol 4 relay nutrisi untuk sistem pencampuran pupuk otomatis.</p>
                <span class="card-link">Buka Dashboard <i class="fas fa-arrow-right"></i></span>
            </a>

            <a href="{{ route('tools') }}" class="card">
                <div class="card-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h2 class="card-title">Alat Lainnya</h2>
                <p class="card-description">Halaman khusus untuk perangkat baru dan pengembangan selanjutnya.</p>
                <span class="card-link">Buka Dashboard <i class="fas fa-arrow-right"></i></span>
            </a>

        </div>
    </div>
</body>
</html>
