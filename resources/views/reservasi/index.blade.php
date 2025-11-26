@extends('layouts.adminlte')

@section('title', 'Data Reservasi')

@section('content')
{{-- CSS KHUSUS HALAMAN INI --}}
<style>
    /* Config Warna */
    :root {
        --gold-primary: #D4AF37; /* Warna Emas */
        --gold-hover: #b89628;
        --bg-dark: #121212;      /* Hitam Pekat Background */
        --card-bg: #1A1A1A;      /* Hitam Kartu */
        --border-color: #333333;
        --text-muted: #a0a0a0;
    }

    /* Typography Overrides */
    h1, h2, h3, h4, h5, h6 { color: #fff !important; }
    .text-gold { color: var(--gold-primary) !important; }
    .text-muted { color: var(--text-muted) !important; }

    /* 1. STATS CARD (Kotak Angka) */
    .stat-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        border-color: var(--gold-primary);
        transform: translateY(-3px);
    }
    .stat-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 3rem !important;
        opacity: 0.1;
        color: #fff;
    }
    .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        margin-top: 5px;
        line-height: 1;
        /* Warna Emas untuk semua angka */
        color: var(--gold-primary); 
    }
    .stat-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
    }

    /* 2. FILTER SECTION */
    .filter-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-control-dark, .form-select-dark {
        background-color: #121212;
        border: 1px solid var(--border-color);
        color: #E0E0E0;
        border-radius: 8px;
        padding: 0.6rem 1rem;
    }
    .form-control-dark:focus, .form-select-dark:focus {
        background-color: #121212;
        border-color: var(--gold-primary);
        color: #fff;
        box-shadow: none;
    }
    .input-group-text-dark {
        background-color: #121212;
        border: 1px solid var(--border-color);
        border-right: none;
        color: var(--text-muted);
    }
    
    /* Tombol Gold */
    .btn-gold {
        background-color: var(--gold-primary);
        color: #000;
        font-weight: 700;
        border: none;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0.6rem 1.2rem;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-gold:hover {
        background-color: var(--gold-hover);
        color: #000;
    }

    /* 3. TABLE CUSTOM */
    .table-container {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        min-height: 300px; /* Biar tidak terlalu gepeng saat kosong */
    }
    .table-dark-custom {
        width: 100%;
        color: #E0E0E0;
        margin-bottom: 0;
    }
    .table-dark-custom thead th {
        background-color: #252525;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        padding: 1rem 1.5rem;
    }
    .table-dark-custom tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.95rem;
    }
    .table-dark-custom tr:last-child td {
        border-bottom: none;
    }
    .table-dark-custom tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.02);
    }
</style>

<div class="container-fluid px-0">
    
    {{-- 1. HEADER HALAMAN --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Selamat Datang, <span class="text-gold">Admin!</span></h1>
            <p class="text-muted mb-0">Manajemen data reservasi pasien hari ini.</p>
        </div>
        {{-- Profile Widget --}}
        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <div class="fw-bold text-white">Basudewa</div>
                <small class="text-muted">Administrator</small>
            </div>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}" 
                 alt="Profile" 
                 class="rounded-circle border border-secondary" 
                 style="width: 45px; height: 45px; object-fit: cover;">
        </div>
    </div>

    {{-- 2. INFO CARDS (STATISTIK) --}}
    <div class="row g-4 mb-4">
        {{-- Total --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">calendar_month</span>
                <div class="stat-label">Total Reservasi</div>
                <div class="stat-value text-gold">150</div> 
            </div>
        </div>
        {{-- Menunggu --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">hourglass_top</span>
                <div class="stat-label">Pasien Menunggu</div>
                <div class="stat-value text-gold">25</div>
            </div>
        </div>
        {{-- Selesai --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">check_circle</span>
                <div class="stat-label">Pasien Selesai</div>
                <div class="stat-value text-gold">120</div>
            </div>
        </div>
        {{-- Batal --}}
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">cancel</span>
                <div class="stat-label">Pasien Batal</div>
                <div class="stat-value text-gold">5</div>
            </div>
        </div>
    </div>

    {{-- 3. FILTER & ACTION BAR --}}
    <div class="filter-card">
        <form action="" method="GET">
            <div class="row g-3 align-items-end">
                {{-- Search --}}
                <div class="col-md-3">
                    <label class="small text-muted mb-2">Cari Pasien / No RM</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-dark">
                            <span class="material-symbols-outlined fs-5">search</span>
                        </span>
                        <input type="text" class="form-control form-control-dark" style="border-left:none;" placeholder="Masukkan kata kunci...">
                    </div>
                </div>

                {{-- Filter Poli (REVISI: DAFTAR POLI BARU) --}}
                <div class="col-md-2">
                    <label class="small text-muted mb-2">Poli</label>
                    <select class="form-select form-select-dark">
                        <option selected>Semua Poli</option>
                        <option value="gigi_umum">Poli Gigi Umum</option>
                        <option value="ortodonti">Poli Spesialis Ortodonti</option>
                        <option value="prosthodonti">Poli Spesialis Prosthodonti</option>
                        <option value="periodonti">Poli Spesialis Periodonti</option>
                        <option value="penyakit_mulut">Poli Spesialis Penyakit Mulut</option>
                    </select>
                </div>

                {{-- Filter Dokter --}}
                <div class="col-md-2">
                    <label class="small text-muted mb-2">Dokter</label>
                    <select class="form-select form-select-dark">
                        <option selected>Semua Dokter</option>
                        <option value="1">drg. Aprilia</option>
                    </select>
                </div>

                {{-- Filter Status Res --}}
                <div class="col-md-2">
                    <label class="small text-muted mb-2">Status Reservasi</label>
                    <select class="form-select form-select-dark">
                        <option selected>Semua Status</option>
                        <option value="menunggu">Menunggu</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>

                {{-- Filter Status Bayar --}}
                <div class="col-md-2">
                    <label class="small text-muted mb-2">Status Pembayaran</label>
                    <select class="form-select form-select-dark">
                        <option selected>Semua Status</option>
                        <option value="lunas">Lunas</option>
                        <option value="belum">Belum Bayar</option>
                    </select>
                </div>

                {{-- Tombol Tambah --}}
                <div class="col-md-1 d-grid">
                    <label class="small text-muted mb-2 d-none d-md-block">&nbsp;</label>
                    {{-- Link dipagar dulu biar ga error backend --}}
                    <a href="#" class="btn btn-gold" title="Tambah Reservasi">
                        <span class="material-symbols-outlined">add</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- 4. TABEL DATA (KOSONG) --}}
    <div class="table-container shadow-sm">
        <div class="table-responsive">
            <table class="table table-dark-custom">
                <thead>
                    <tr>
                        <th>No RM</th>
                        <th>Pasien</th>
                        <th>Poli & Dokter</th>
                        <th>Jadwal</th>
                        <th class="text-center">Status Reservasi</th>
                        <th class="text-center">Pembayaran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <span class="material-symbols-outlined fs-1 mb-2 opacity-50">folder_open</span>
                            <p class="mb-0">Belum ada data reservasi.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Pagination (Tampilan Saja) --}}
    <div class="d-flex justify-content-end mt-4">
        <nav>
            <ul class="pagination pagination-sm">
                <li class="page-item disabled"><a class="page-link bg-transparent border-secondary text-muted" href="#">&laquo;</a></li>
                <li class="page-item active"><a class="page-link bg-gold border-gold text-dark fw-bold" href="#">1</a></li>
                <li class="page-item"><a class="page-link bg-transparent border-secondary text-gold" href="#">2</a></li>
                <li class="page-item"><a class="page-link bg-transparent border-secondary text-muted" href="#">&raquo;</a></li>
            </ul>
        </nav>
    </div>

</div>
@endsection