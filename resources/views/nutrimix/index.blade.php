<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrimix Controller</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/nutrimix.css') }}">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <div class="brand">
                    <img src="{{ asset('images/vita root.png') }}" alt="VitaRoot" class="brand-logo">
                    <div>
                        <h1>Nutrimix Controller</h1>
                        <p class="subtitle">Kontrol proses pencampuran nutrisi berbasis target berat.</p>
                    </div>
                </div>
                <span class="badge" id="connectionBadge">Menghubungkan...</span>
            </div>
        </header>

        <main class="dashboard">
            <!-- Status Panel -->
            <div class="status-panel">
                <div class="status-item">
                    <i class="fas fa-wifi"></i>
                    <div>
                        <span class="status-label">Koneksi</span>
                        <span class="status-value" id="wifiStatus">Mengecek...</span>
                    </div>
                </div>
                <div class="status-item">
                    <i class="fas fa-database"></i>
                    <div>
                        <span class="status-label">Firebase</span>
                        <span class="status-value" id="firebaseStatus">Mengecek...</span>
                    </div>
                </div>
                <div class="status-item">
                    <i class="fas fa-gear"></i>
                    <div>
                        <span class="status-label">Status Proses</span>
                        <span class="status-value" id="processState">Idle</span>
                    </div>
                </div>
                <div class="status-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <span class="status-label">Last Seen</span>
                        <span class="status-value" id="lastSeen">--</span>
                    </div>
                </div>
            </div>

            <div class="control-grid">
                <section class="control-card">
                    <div class="card-title">
                        <i class="fas fa-bullseye"></i>
                        <h2>Auto Mix</h2>
                    </div>
                    <p class="card-subtitle">Set target berat dan mulai proses otomatis.</p>
                    <div class="field">
                        <label for="targetWeight">Target Berat (gram)</label>
                        <input id="targetWeight" type="number" min="1" step="1" placeholder="Contoh: 250" />
                    </div>
                    <div class="button-row">
                        <button class="btn primary" id="startAuto">Mulai Otomatis</button>
                        <button class="btn outline" id="stopAuto">Stop</button>
                    </div>
                    <div class="progress-wrap">
                        <div class="progress-label">
                            <span>Progress Berat</span>
                            <span id="progressLabel">0%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                    </div>
                    <div class="mini-metrics">
                        <div>
                            <span class="metric-label">Berat Saat Ini</span>
                            <span class="metric-value" id="currentWeight">0 g</span>
                        </div>
                        <div>
                            <span class="metric-label">Target Aktif</span>
                            <span class="metric-value" id="activeTarget">--</span>
                        </div>
                    </div>
                </section>

                <section class="control-card">
                    <div class="card-title">
                        <i class="fas fa-toggle-on"></i>
                        <h2>Manual Control</h2>
                    </div>
                    <p class="card-subtitle">Kontrol screw, trimmer, dan servo secara manual.</p>
                    <div class="toggle-row">
                        <div>
                            <span class="toggle-label">Screw Motor</span>
                            <span class="toggle-status" id="screwStatus">OFF</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="screwToggle">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <span class="toggle-label">Trimmer</span>
                            <span class="toggle-status" id="trimmerStatus">OFF</span>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="trimmerToggle">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <span class="toggle-label">Servo Trimmer</span>
                            <span class="toggle-status" id="servoStatus">CLOSE</span>
                        </div>
                        <div class="button-row">
                            <button class="btn ghost" id="servoOpen">Buka</button>
                            <button class="btn ghost" id="servoClose">Tutup</button>
                        </div>
                    </div>
                </section>
            </div>

            <section class="monitor-grid">
                <div class="monitor-card">
                    <h3>Monitoring</h3>
                    <div class="monitor-item">
                        <span>HX711</span>
                        <span id="hxStatus">--</span>
                    </div>
                    <div class="monitor-item">
                        <span>Berat Aktual</span>
                        <span id="weightValue">0 g</span>
                    </div>
                    <div class="monitor-item">
                        <span>Target Berat</span>
                        <span id="targetValue">--</span>
                    </div>
                    <div class="monitor-item">
                        <span>Mode</span>
                        <span id="deviceMode">Auto</span>
                    </div>
                    <div class="monitor-item">
                        <span>Error</span>
                        <span id="errorStatus">-</span>
                    </div>
                </div>
                <div class="monitor-card">
                    <h3>Aktuator</h3>
                    <div class="monitor-item">
                        <span>Screw</span>
                        <span id="screwState">OFF</span>
                    </div>
                    <div class="monitor-item">
                        <span>Trimmer</span>
                        <span id="trimmerState">OFF</span>
                    </div>
                    <div class="monitor-item">
                        <span>Servo</span>
                        <span id="servoState">CLOSE</span>
                    </div>
                    <div class="monitor-item">
                        <span>Durasi Trimmer</span>
                        <span id="trimmerDuration">--</span>
                    </div>
                    <div class="monitor-item">
                        <span>Estimasi Selesai</span>
                        <span id="etaValue">--</span>
                    </div>
                </div>
            </section>

            <!-- Info Panel -->
            <div class="info-panel">
                <p><i class="fas fa-info-circle"></i> Perintah otomatis dan manual akan dikirim ke ESP. Pastikan perangkat online sebelum mengirim perintah.</p>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; {{ date('Y') }} Smart Buncop - Nutrimix Controller</p>
        </footer>
    </div>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-database-compat.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/nutrimix.js') }}"></script>
</body>
</html>
