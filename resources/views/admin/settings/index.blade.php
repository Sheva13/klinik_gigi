@extends('layouts.adminlte')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
crossorigin=""/>

<style>
    /* Card Custom (Dark Theme Match) */
    .card-custom {
        background-color: #1A1A1A;
        border: 1px solid #333333;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    .card-header-custom {
        background-color: #2C2C2C;
        border-bottom: 1px solid #333333;
        padding: 15px 20px;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
    }
    .card-title-custom {
        color: #f5c542; /* Gold */
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    /* Form Inputs (Dark Theme) */
    .form-control-dark {
        background-color: #0f0f0f;
        border: 1px solid #444;
        color: #e0e0e0;
        border-radius: 8px;
        padding: 10px 15px;
    }
    .form-control-dark:focus {
        background-color: #0f0f0f;
        border-color: #f5c542;
        color: #fff;
        box-shadow: 0 0 0 0.2rem rgba(245, 197, 66, 0.25);
    }
    .form-label-custom {
        color: #a0a0a0;
        font-weight: 500;
        margin-bottom: 8px;
    }
    .helper-text {
        color: #666;
        font-size: 0.8rem;
        margin-top: 5px;
    }

    /* Button Gold */
    .btn-gold-block {
        background-image: linear-gradient(to right, #f5c542, #e4a93c);
        color: #121212;
        font-weight: 800;
        border: none;
        padding: 15px;
        border-radius: 10px;
        width: 100%;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s;
    }
    .btn-gold-block:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        color: #000;
    }

    /* Map Container */
    #map {
        height: 300px;
        width: 100%;
        border-radius: 8px;
        border: 1px solid #444;
        margin-top: 10px;
    }
</style>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col-sm-6">
                <h1 class="m-0 fw-bold" style="color: #e0e0e0; font-size: 2rem;">Pengaturan Aplikasi</h1>
                <p class="text-secondary mb-0">Kelola konfigurasi harga dan lokasi klinik.</p>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" 
                 style="background-color: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.4); color: #4ade80;">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            
            <div class="row">
                <!-- LOCATION SETTINGS -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-custom h-100">
                        <div class="card-header-custom">
                            <h3 class="card-title-custom">
                                <span class="material-symbols-outlined">location_on</span>
                                Titik Lokasi Klinik
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-dark border-secondary bg-opacity-25 mb-3">
                                <i class="fas fa-info-circle text-warning me-2"></i>
                                <span class="text-secondary text-sm">Geser pin pada peta untuk menentukan lokasi.</span>
                            </div>

                            <!-- Map Container -->
                            <div id="map" class="mb-4"></div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label-custom">Latitude</label>
                                        <input type="text" name="settings[clinic_lat]" id="lat"
                                               class="form-control form-control-dark" 
                                               value="{{ $settings['clinic_lat']->value ?? '' }}"
                                               readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label-custom">Longitude</label>
                                        <input type="text" name="settings[clinic_lng]" id="lng"
                                               class="form-control form-control-dark" 
                                               value="{{ $settings['clinic_lng']->value ?? '' }}"
                                               readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRICING SETTINGS -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-custom h-100">
                        <div class="card-header-custom">
                            <h3 class="card-title-custom">
                                <span class="material-symbols-outlined">payments</span>
                                Biaya Layanan HomeCare
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-group mb-4">
                                <label class="form-label-custom">Biaya Dasar Layanan (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-secondary">Rp</span>
                                    <input type="number" name="settings[homecare_base_fee]" 
                                           class="form-control form-control-dark font-weight-bold" 
                                           style="font-size: 1.1rem; color: #4ade80;"
                                           value="{{ $settings['homecare_base_fee']->value ?? '' }}">
                                </div>
                                <div class="helper-text">Biaya jasa tetap per kunjungan (di luar transport).</div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label-custom">Harga Per Kilometer (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-secondary">Rp</span>
                                    <input type="number" name="settings[price_per_km]" 
                                           class="form-control form-control-dark font-weight-bold"
                                           style="font-size: 1.1rem; color: #f5c542;" 
                                           value="{{ $settings['price_per_km']->value ?? '' }}">
                                </div>
                                <div class="helper-text">Tarif transport per km (sistem menghitung PP otomatis).</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                     <button type="submit" class="btn btn-gold-block shadow-lg">
                        <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">save</span>
                        Simpan Semua Pengaturan
                    </button>
                    <p class="text-center text-muted mt-3 mb-5">
                        <small>Perubahan akan langsung berpengaruh pada aplikasi Android.</small>
                    </p>
                </div>
            </div>

        </form>
    </div>
</section>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
crossorigin=""></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ambil nilai awal dari input
        var initialLat = parseFloat(document.getElementById('lat').value) || -7.0005141; // Default Semarang if empty
        var initialLng = parseFloat(document.getElementById('lng').value) || 110.4250683;

        // Inisialisasi Map
        var map = L.map('map').setView([initialLat, initialLng], 15);

        // Tambahkan Tile Layer (Gratis OPENSTREETMAP)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Tambahkan Marker yang bisa digeser (Draggable)
        var marker = L.marker([initialLat, initialLng], {
            draggable: true
        }).addTo(map);

        // Event saat marker digeser
        marker.on('dragend', function(event) {
            var position = marker.getLatLng();
            document.getElementById('lat').value = position.lat; // Update hidden input
            document.getElementById('lng').value = position.lng;
        });

        // Event saat peta diklik (pindah marker ke situ)
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
        });
    });
</script>
@endsection

