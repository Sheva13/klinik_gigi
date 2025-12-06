@extends('layouts.adminlte')

@section('title', 'Manajemen Antrian Pasien')

@section('content')

<style>
    /* --- CONFIG PREMIUM DARK THEME --- */
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --bg-body: #121212;     
        --bg-card: #1E1E1E;     
        --bg-input: #252525;
        --border-color: #333333;
        --text-white: #ffffff;
        --text-muted: #b0b0b0; /* Text muted dibikin lebih terang */
    }

    /* Override Body Background & Global Text */
    body, .content-wrapper {
        background-color: var(--bg-body) !important;
        color: var(--text-white) !important;
    }
    
    /* Paksa semua Heading jadi Putih */
    h1, h2, h3, h4, h5, h6 { color: #ffffff !important; }

    /* CARD STYLE */
    .bg-card { 
        background-color: var(--bg-card) !important; 
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* INPUT FIELDS */
    .input-group-text-dark {
        background-color: var(--bg-input);
        border: 1px solid var(--border-color);
        border-right: none;
        color: var(--text-muted);
    }
    .form-control-dark, .form-select-dark {
        background-color: var(--bg-input);
        border: 1px solid var(--border-color);
        color: #fff !important; /* Paksa text input putih */
        border-radius: 8px;
        padding: 0.7rem 1rem;
    }
    .form-control-dark:focus, .form-select-dark:focus {
        background-color: var(--bg-input);
        border-color: var(--gold-primary);
        color: #fff !important;
        box-shadow: none;
    }
    /* Placeholder color fix */
    .form-control-dark::placeholder { color: #666; }

    /* BUTTONS */
    .btn-gold {
        background-color: var(--gold-primary);
        color: #000;
        font-weight: 700;
        border: none;
        border-radius: 8px;
        padding: 0.8rem 1rem;
        width: 100%;
        transition: all 0.3s;
    }
    .btn-gold:hover { 
        background-color: var(--gold-hover); 
        transform: translateY(-2px);
    }

    /* --- RINGKASAN BOX (FIX FONT WARNA) --- */
    .summary-box {
        background-color: #2b2b2b; 
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #444;
        position: relative;
        overflow: hidden;
        color: #ffffff !important; /* PAKSA TEXT PUTIH */
    }
    /* Pastikan elemen text di dalam box berwarna terang */
    .summary-box .summary-label {
        font-size: 0.85rem;
        color: #cccccc !important; /* Abu terang */
        margin-bottom: 5px;
        display: block;
    }
    .summary-box h2 {
        color: #ffffff !important; /* Angka Putih */
        font-weight: 800;
        margin-bottom: 0;
    }
    
    /* Garis indikator warna di kiri */
    .box-warning { border-left: 5px solid #ffc107; }
    .box-info { border-left: 5px solid #0dcaf0; }
    .box-success { border-left: 5px solid #198754; }

    /* TABEL STYLE (PREMIUM DARK) */
    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }
    .table-dark-custom {
        width: 100%;
        background-color: var(--bg-card);
        color: var(--text-white);
        margin-bottom: 0;
    }
    .table-dark-custom thead th {
        background-color: #252525;
        color: var(--text-muted);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 1.2rem 1rem;
        border-bottom: 1px solid var(--border-color);
        font-weight: 600;
    }
    .table-dark-custom tbody td {
        background-color: var(--bg-card);
        border-bottom: 1px solid #2a2a2a;
        padding: 1.2rem 1rem;
        vertical-align: middle;
        color: #eeeeee !important; /* Paksa text tabel putih/terang */
    }
    .table-dark-custom tbody tr:hover td {
        background-color: #252525; /* Hover effect */
    }
    
    /* TYPOGRAPHY IN TABLE */
    .queue-number {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--gold-primary);
        font-family: 'Courier New', monospace;
    }
    .patient-name { font-weight: 600; font-size: 0.95rem; display: block; color: #fff !important; }
    .rm-number { font-size: 0.75rem; color: #aaa; background: #333; padding: 2px 6px; border-radius: 4px; }
    
    .poli-badge {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: rgba(212, 175, 55, 0.1); 
        color: var(--gold-primary);
        padding: 4px 8px;
        border-radius: 4px;
        border: 1px solid rgba(212, 175, 55, 0.3);
        margin-bottom: 4px;
        display: inline-block;
    }
    .dokter-name { font-size: 0.9rem; color: #ddd; }

    /* STATUS PILLS */
    .status-pill {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .pill-menunggu { background: rgba(255, 193, 7, 0.1); color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.2); }
    .pill-proses { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; border: 1px solid rgba(13, 202, 240, 0.2); }
    .pill-selesai { background: rgba(25, 135, 84, 0.1); color: #198754; border: 1px solid rgba(25, 135, 84, 0.2); }
    .pill-batal { background: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2); }
    
    .dot { width: 6px; height: 6px; border-radius: 50%; }
</style>

<div class="container-fluid px-0">

    {{-- HEADER PAGE --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem; color: #fff !important;">Manajemen <span style="color: var(--gold-primary);">Antrian</span></h1>
            <p style="color: #aaa;">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="d-flex align-items-center gap-3">
             <div class="text-end d-none d-md-block">
                <div class="fw-bold text-white">{{ Auth::guard('admin')->user()->nama ?? 'Admin' }}</div>
                <small style="color: #aaa;">Administrator</small>
            </div>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}" class="rounded-circle border border-secondary" style="width: 45px; height: 45px; object-fit: cover;">
        </div>
    </div>

    <div class="row g-4">
        
        {{-- === KOLOM KIRI: FORM & RINGKASAN === --}}
        <div class="col-lg-4">
            
            {{-- CARD 1: FORM BUAT NOMOR ANTRIAN --}}
            <div class="bg-card mb-4">
                <h5 class="fw-bold text-white mb-4">
                    <span class="material-symbols-outlined align-middle me-2 text-gold">confirmation_number</span>
                    Buat Nomor Antrian
                </h5>
                
                <form action="{{ route('reservasi.admin.create') }}" method="GET">
                    <div class="mb-3">
                        <label class="small mb-2" style="color: #aaa;">Cari Pasien</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-dark">
                                <span class="material-symbols-outlined fs-6">search</span>
                            </span>
                            <input type="text" class="form-control form-control-dark" placeholder="Ketik nama atau ID..." readonly style="cursor: pointer;" onclick="this.closest('form').submit();">
                        </div>
                    </div>
                    <div class="mb-4">
                         <label class="small mb-2" style="color: #aaa;">Dokter Tujuan</label>
                        <select class="form-select form-select-dark" disabled>
                            <option>Pilih di langkah selanjutnya...</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-gold">
                        <span class="material-symbols-outlined fs-5 align-middle">add_circle</span> Buat Antrian Baru
                    </button>
                </form>
            </div>

            {{-- CARD 2: RINGKASAN HARI INI (FIX FONT COLOR) --}}
            <div class="bg-card">
                <h5 class="fw-bold text-white mb-4">Ringkasan Antrian</h5>

                {{-- Box Menunggu --}}
                <div class="summary-box box-warning">
                    <div>
                        <span class="summary-label">Menunggu</span>
                        <h2 class="fw-bold mb-0">{{ $stats['menunggu'] ?? 0 }}</h2>
                    </div>
                    <span class="material-symbols-outlined text-warning fs-1 opacity-50">hourglass_top</span>
                </div>

                {{-- Box Sedang Diperiksa --}}
                <div class="summary-box box-info">
                    <div>
                        <span class="summary-label">Sedang Diperiksa</span>
                        <h2 class="fw-bold mb-0">{{ $stats['diproses'] ?? 0 }}</h2>
                    </div>
                    <span class="material-symbols-outlined text-info fs-1 opacity-50">medical_services</span>
                </div>

                {{-- Box Selesai --}}
                <div class="summary-box box-success">
                    <div>
                        <span class="summary-label">Selesai</span>
                        <h2 class="fw-bold mb-0">{{ $stats['selesai'] ?? 0 }}</h2>
                    </div>
                    <span class="material-symbols-outlined text-success fs-1 opacity-50">check_circle</span>
                </div>
            </div>
        </div>

        {{-- === KOLOM KANAN: TABEL DAFTAR ANTRIAN === --}}
        <div class="col-lg-8">
            <div class="bg-card h-100 p-0 overflow-hidden">
                
                {{-- Toolbar Tabel --}}
                <div class="p-4 border-bottom border-secondary d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="fw-bold text-white mb-0">Daftar Antrian Hari Ini</h5>
                    <form action="{{ route('reservasi.admin.antrian') }}" method="GET">
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text input-group-text-dark bg-transparent">
                                <span class="material-symbols-outlined fs-6">search</span>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-dark bg-transparent" placeholder="Cari nama / no antrian..." style="border-left: none;">
                        </div>
                    </form>
                </div>

                {{-- TABEL HITAM --}}
                <div class="table-responsive">
                    <table class="table table-dark-custom mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">NO ANTRIAN</th>
                                <th>NAMA PASIEN</th>
                                <th>POLI & DOKTER</th>
                                <th>JADWAL</th>
                                <th class="text-center pe-4">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($antrian as $item)
                                <tr>
                                    {{-- 1. NO ANTRIAN --}}
                                    <td class="ps-4">
                                        <div class="queue-number">{{ $item->no_antrian ?? '-' }}</div>
                                    </td>

                                    {{-- 2. NAMA PASIEN --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            {{-- Initials Avatar --}}
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-3 text-white fw-bold" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                                {{ substr($item->rekamMedis->nama ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="patient-name">{{ $item->rekamMedis->nama ?? 'Tanpa Nama' }}</span>
                                                <span class="rm-number">RM: {{ $item->rekamMedis->rekam_medis ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- 3. POLI & DOKTER (Digabung biar rapi) --}}
                                    <td>
                                        <div>
                                            {{-- Logic sederhana untuk Poli --}}
                                            <span class="poli-badge">
                                                {{ $item->jadwal->poli->nama_poli ?? ($item->dokter->poli->nama_poli ?? 'Poli Gigi') }}
                                            </span>
                                            <div class="dokter-name">{{ $item->dokter->nama ?? '-' }}</div>
                                        </div>
                                    </td>

                                    {{-- 4. JADWAL --}}
                                    <td>
                                        <div class="text-white fw-bold">{{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }} <span style="color: #888; font-size: 0.8rem; font-weight: normal;">WIB</span></div>
                                        <small style="color: #888; font-size: 0.75rem;">Estimasi</small>
                                    </td>

                                    {{-- 5. STATUS (Tanpa Aksi Dropdown) --}}
                                    <td class="text-center pe-4">
                                        @php
                                            $s = $item->status_reservasi;
                                            $class = match($s) {
                                                'menunggu' => 'pill-menunggu',
                                                'dalam_proses' => 'pill-proses',
                                                'selesai' => 'pill-selesai',
                                                'batal' => 'pill-batal',
                                                default => 'pill-menunggu'
                                            };
                                            $dotColor = match($s) {
                                                'menunggu' => '#ffc107',
                                                'dalam_proses' => '#0dcaf0',
                                                'selesai' => '#198754',
                                                'batal' => '#dc3545',
                                                default => '#ffc107'
                                            };
                                            $label = match($s) {
                                                'dalam_proses' => 'Diperiksa',
                                                default => ucfirst($s)
                                            };
                                        @endphp
                                        <span class="status-pill {{ $class }}">
                                            <span class="dot" style="background-color: {{ $dotColor }}"></span>
                                            {{ $label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <span class="material-symbols-outlined fs-1 opacity-25 d-block mb-3" style="color: #888;">folder_off</span>
                                        <p class="mb-0" style="color: #888;">Belum ada antrian pasien hari ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- End Table --}}

            </div>
        </div>
    </div>
</div>
@endsection