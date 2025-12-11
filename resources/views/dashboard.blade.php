@extends('layouts.adminlte')

@section('title', 'Dashboard')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* --- CUSTOM DARK & GOLD THEME --- */
    :root {
        --primary-gold: #f5c542;
        --bg-dark: #121212;
        --card-dark: #242424;
        --text-grey: #9ca3af;
    }

    .content-wrapper {
        background-color: var(--bg-dark) !important;
        color: white;
    }

    /* Kartu Statistik */
    .stat-card {
        background-color: var(--card-dark);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 20px;
        transition: transform 0.2s, border-color 0.2s;
        height: 100%; /* Pastikan tinggi konsisten */
    }
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary-gold);
    }
    .stat-icon {
        color: var(--primary-gold);
        font-size: 2rem;
    }
    .stat-title {
        color: var(--text-grey);
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 5px;
    }
    .stat-value {
        color: white;
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
    }

    /* Grafik Container */
    .chart-container {
        background-color: var(--card-dark);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 20px;
        margin-top: 30px;
    }

    /* Wrapper Canvas Grafik agar Responsif */
    .chart-canvas-wrapper {
        position: relative; 
        width: 100%;
        height: 400px; /* Default Desktop */
    }

    /* Override Header text */
    h1, h2, h3, h4, h5, h6 {
        color: white !important;
    }

    /* --- RESPONSIVE ADJUSTMENTS (Mobile Optimization) --- */
    @media (max-width: 768px) {
        .stat-card {
            padding: 15px; /* Kurangi padding di HP */
        }
        .stat-value {
            font-size: 1.75rem; /* Kecilkan angka di HP */
        }
        .stat-icon {
            font-size: 1.75rem;
        }
        .stat-title {
            font-size: 0.85rem;
        }
        .chart-container {
            padding: 15px;
            margin-top: 20px;
        }
        .chart-canvas-wrapper {
            height: 300px; /* Grafik lebih pendek di HP */
        }
        h2 {
            font-size: 1.5rem; /* Judul dashboard lebih kecil */
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid pt-4">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard Overview</h2>
            <p class="text-secondary mb-0">Ringkasan aktivitas klinik hari ini.</p>
        </div>
        </div>

    <div class="row g-3 g-lg-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="stat-title">Reservasi Hari Ini</span>
                    <span class="material-symbols-outlined stat-icon">today</span>
                </div>
                <div class="mt-3">
                    <h3 class="stat-value">{{ $reservasiHariIni }}</h3>
                    <small class="text-success d-flex align-items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 14px;">arrow_upward</span> 
                        Data terbaru
                    </small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="stat-title">Total Promo Aktif</span>
                    <span class="material-symbols-outlined stat-icon">sell</span>
                </div>
                <div class="mt-3">
                    <h3 class="stat-value">{{ $totalPromo }}</h3>
                    <small class="text-secondary">Siap digunakan</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="stat-title">Dokter Terdaftar</span>
                    <span class="material-symbols-outlined stat-icon">stethoscope</span>
                </div>
                <div class="mt-3">
                    <h3 class="stat-value">{{ $totalDokter }}</h3>
                    <small class="text-secondary">Total dokter klinik</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card d-flex flex-column justify-content-between">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="stat-title">Kunjungan Home Care</span>
                    <span class="material-symbols-outlined stat-icon">home_health</span>
                </div>
                <div class="mt-3">
                    <h3 class="stat-value">{{ $homeCare }}</h3>
                    <small class="text-secondary">Bulan ini</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="chart-container">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
                    <h5 class="mb-0 fw-bold">Statistik Kesibukan Dokter</h5>
                    <span class="badge bg-warning text-dark">Top 10 Jadwal Terbanyak</span>
                </div>
                
                <div class="chart-canvas-wrapper">
                    <canvas id="dokterChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const labels = @json($chartLabels);
        const dataValues = @json($chartValues);

        const ctx = document.getElementById('dokterChart').getContext('2d');
        
        // Setup Chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Jadwal Praktik',
                    data: dataValues,
                    backgroundColor: 'rgba(245, 197, 66, 0.7)',
                    borderColor: '#f5c542',
                    borderWidth: 1,
                    borderRadius: 4,
                    barThickness: 'flex',
                    maxBarThickness: 50,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { 
                            color: '#fff',
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            // Opsional: Menambahkan kata "Jadwal" di tooltip saat di-hover
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { 
                            color: '#9ca3af', 
                            font: { size: 11 },
                            // --- BAGIAN INI YANG DIPERBAIKI ---
                            stepSize: 1,   // Memaksa kelipatan 1 (1, 2, 3, dst)
                            precision: 0   // Menghilangkan angka desimal (koma)
                            // ----------------------------------
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#fff', font: { size: 11 } }
                    }
                }
            }
        });
    });
</script>
@endsection