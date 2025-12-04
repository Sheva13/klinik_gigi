@extends('layouts.adminlte')

@section('title', 'Data Reservasi')

@section('content')

<style>
    /* Config Warna */
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --bg-dark: #121212;
        --card-bg: #1A1A1A;
        --border-color: #333333;
        --text-muted: #a0a0a0;
    }

    /* Typography Overrides */
    h1, h2, h3, h4, h5, h6 { color: #fff !important; }
    .text-gold { color: var(--gold-primary) !important; }
    .text-muted { color: var(--text-muted) !important; }

    /* 1. STATS CARD */
    .stat-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s;
        height: 100%; /* Agar tinggi kartu seragam */
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
        color: var(--gold-primary); 
    }
    .stat-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
    }
    
    /* Warna Khusus Value Stats */
    .text-warning { color: #ffc107 !important; }
    .text-info { color: #17a2b8 !important; }
    .text-success { color: #28a745 !important; }
    .text-danger { color: #dc3545 !important; }

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
        min-height: 300px;
    }
    .table-dark-custom {
        width: 100%;
        color: #E0E0E0;
        margin-bottom: 0;
        --bs-table-bg: transparent; 
        background-color: transparent;
    }
    .table-dark-custom thead th {
        background-color: #252525 !important;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        padding: 1rem 1.5rem;
        white-space: nowrap; /* Agar header tidak turun baris */
    }
    .table-dark-custom tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.95rem;
        background-color: var(--card-bg) !important;
        color: #E0E0E0 !important; 
    }
    .table-dark-custom tr:last-child td {
        border-bottom: none;
    }
    .table-dark-custom tbody tr:hover td {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    /* Badge Custom */
    .badge {
        padding: 0.5em 0.8em;
        font-weight: 600;
        border-radius: 6px;
    }
    .bg-warning { background-color: rgba(255, 193, 7, 0.2) !important; color: #ffc107 !important; border: 1px solid #ffc107; }
    .bg-info { background-color: rgba(23, 162, 184, 0.2) !important; color: #17a2b8 !important; border: 1px solid #17a2b8; }
    .bg-success { background-color: rgba(40, 167, 69, 0.2) !important; color: #28a745 !important; border: 1px solid #28a745; }
    .bg-danger { background-color: rgba(220, 53, 69, 0.2) !important; color: #dc3545 !important; border: 1px solid #dc3545; }
    .bg-secondary { background-color: rgba(108, 117, 125, 0.2) !important; color: #adb5bd !important; border: 1px solid #adb5bd; }
</style>

<div class="container-fluid px-0">
    
    {{-- 1. HEADER HALAMAN --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Selamat Datang, <span class="text-gold">Admin!</span></h1>
            <p class="text-muted mb-0">Manajemen data reservasi pasien hari ini.</p>
        </div>
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

   {{-- 2. INFO CARDS --}}
    <div class="row g-4 mb-4">
        {{-- Total --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">calendar_month</span>
                <div class="stat-label">Total</div>
                <div class="stat-value text-gold">{{ $stats['total'] }}</div> 
            </div>
        </div>

        {{-- Menunggu --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">hourglass_top</span>
                <div class="stat-label">Menunggu</div>
                <div class="stat-value text-warning">{{ $stats['menunggu'] }}</div>
            </div>
        </div>

        {{-- 🔥 TAMBAHAN: DIPROSES --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">stethoscope</span>
                <div class="stat-label">Diproses</div>
                {{-- Mengambil data 'diproses' dari controller (pastikan controller kirim key ini) --}}
                <div class="stat-value text-info">{{ $stats['diproses'] ?? 0 }}</div> 
            </div>
        </div>

        {{-- Selesai --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">check_circle</span>
                <div class="stat-label">Selesai</div>
                <div class="stat-value text-success">{{ $stats['selesai'] }}</div>
            </div>
        </div>

        {{-- Batal --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">cancel</span>
                <div class="stat-label">Batal</div>
                <div class="stat-value text-danger">{{ $stats['batal'] }}</div>
            </div>
        </div>
    </div>

    {{-- 3. FILTER & ACTION BAR --}}
    <div class="filter-card">
        <form action="{{ route('reservasi.admin.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                {{-- SEARCH --}}
                <div class="col-md-3">
                    <label class="small text-muted mb-2">Cari Pasien / No RM</label>
                    <div class="input-group">
                        <button type="submit" class="input-group-text input-group-text-dark btn btn-dark">
                            <span class="material-symbols-outlined fs-5">search</span>
                        </button>
                        <input type="text" name="no_rm" value="{{ request('no_rm') }}" class="form-control form-control-dark" style="border-left:none;" placeholder="Masukkan kata kunci...">
                    </div>
                </div>

                {{-- FILTER POLI --}}
                <div class="col-md-2">
                    <label class="small text-muted mb-2">Poli</label>
                    <select name="poli_id" class="form-select form-select-dark" onchange="this.form.submit()">
                        <option value="semua" {{ request('poli_id') == 'semua' ? 'selected' : '' }}>Semua Poli</option>
                        @foreach($polis as $poli)
                            <option value="{{ $poli->kode_poli }}" {{ request('poli_id') == $poli->kode_poli ? 'selected' : '' }}>
                                {{ $poli->nama_poli }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- FILTER DOKTER --}}
                <div class="col-md-2">
                    <label class="small text-muted mb-2">Dokter</label>
                    <select name="dokter_id" class="form-select form-select-dark" onchange="this.form.submit()">
                        <option value="semua" {{ request('dokter_id') == 'semua' ? 'selected' : '' }}>Semua Dokter</option>
                        @foreach($dokters as $dokter)
                            <option value="{{ $dokter->kode_dokter }}" {{ request('dokter_id') == $dokter->kode_dokter ? 'selected' : '' }}>
                                {{ $dokter->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- FILTER STATUS RESERVASI --}}
                <div class="col-md-2">
                    <label class="small text-muted mb-2">Status Reservasi</label>
                    <select name="status_reservasi" class="form-select form-select-dark" onchange="this.form.submit()">
                        <option value="semua" {{ request('status_reservasi') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="menunggu" {{ request('status_reservasi') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="dalam_proses" {{ request('status_reservasi') == 'dalam_proses' ? 'selected' : '' }}>Diproses</option>
                        <option value="selesai" {{ request('status_reservasi') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="batal" {{ request('status_reservasi') == 'batal' ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>

                {{-- FILTER STATUS PEMBAYARAN --}}
                <div class="col-md-2">
                    <label class="small text-muted mb-2">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-select form-select-dark" onchange="this.form.submit()">
                        <option value="semua" {{ request('status_pembayaran') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="terverifikasi" {{ request('status_pembayaran') == 'terverifikasi' ? 'selected' : '' }}>Lunas</option>
                        <option value="menunggu_pembayaran" {{ request('status_pembayaran') == 'menunggu_pembayaran' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="menunggu_verifikasi" {{ request('status_pembayaran') == 'menunggu_verifikasi' ? 'selected' : '' }}>Cek Bukti</option>
                        <option value="gagal" {{ request('status_pembayaran') == 'gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>

                {{-- TOMBOL TAMBAH --}}
                <div class="col-md-1 d-grid">
                    <label class="small text-muted mb-2 d-none d-md-block">&nbsp;</label>
                    <a href="{{ route('reservasi.admin.create') }}" class="btn btn-gold" title="Tambah Reservasi">
                        <span class="material-symbols-outlined">add</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- 4. TABEL DATA --}}
    <div class="table-container shadow-sm">
        <div class="table-responsive">
            <table class="table table-dark-custom">
                <thead>
                    <tr>
                        <th>No RM / Kode</th>
                        <th>Pasien</th>
                        <th>Poli & Dokter</th>
                        <th>Jadwal</th>
                        <th class="text-center">Status Reservasi</th>
                        <th class="text-center">Pembayaran</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        <tr>
                            {{-- NO RM --}}
                            <td>
                                <div class="fw-bold">{{ $item->rekamMedis->rekam_medis ?? '-' }}</div>
                                <small class="text-muted">{{ $item->no_pemeriksaan ?? 'Belum Ada' }}</small>
                            </td>

                            {{-- PASIEN --}}
                            <td>
                                {{ $item->rekamMedis->nama ?? 'Data Pasien Hilang' }}
                            </td>

                            {{-- POLI & DOKTER --}}
                            <td>
                                <div class="text-gold">{{ $item->dokter->masterPoli->nama_poli ?? '-' }}</div>
                                <small class="text-muted">{{ $item->dokter->nama ?? '-' }}</small>
                            </td>

                            {{-- JADWAL --}}
                            <td>
                                <div>{{ \Carbon\Carbon::parse($item->tanggal_pesan)->translatedFormat('d M Y') }}</div>
                                <small class="text-muted">
                                    @php $jadwal = $item->jadwal; @endphp
                                    @if($jadwal)
                                        @php
                                            $hari = $jadwal->hari;
                                            $namaHari = $hari;
                                            if(is_numeric($hari)) {
                                                $mapHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                                                $namaHari = $mapHari[$hari] ?? $hari;
                                            }
                                        @endphp
                                        {{ $namaHari }}, {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                                    @else
                                        <span class="text-danger font-italic">Jadwal Terhapus</span>
                                    @endif
                                </small>
                            </td>

                            {{-- STATUS RESERVASI (SESUAI DB) --}}
                            <td class="text-center">
                                @php
                                    $resStatus = $item->status_reservasi;
                                    $resColor = match($resStatus) {
                                        'menunggu'      => 'bg-warning text-dark',
                                        'dalam_proses'  => 'bg-info text-dark',
                                        'selesai'       => 'bg-success',
                                        'batal'         => 'bg-danger',
                                        default         => 'bg-secondary'
                                    };
                                    $resLabel = match($resStatus) {
                                        'menunggu'      => 'Menunggu',
                                        'dalam_proses'  => 'Diproses',
                                        'selesai'       => 'Selesai',
                                        'batal'         => 'Batal',
                                        default         => ucfirst($resStatus)
                                    };
                                @endphp
                                <span class="badge {{ $resColor }}">{{ $resLabel }}</span>
                            </td>

                            {{-- STATUS PEMBAYARAN (SESUAI DB) --}}
                            <td class="text-center">
                                @php
                                    $payStatus = $item->status_pembayaran;
                                    $payColor = match($payStatus) {
                                        'terverifikasi'       => 'bg-success',
                                        'menunggu_pembayaran' => 'bg-secondary',
                                        'menunggu_verifikasi' => 'bg-warning text-dark',
                                        'gagal'               => 'bg-danger',
                                        default               => 'bg-secondary'
                                    };
                                    $payLabel = match($payStatus) {
                                        'terverifikasi'       => 'Lunas',
                                        'menunggu_pembayaran' => 'Belum Bayar',
                                        'menunggu_verifikasi' => 'Cek Bukti',
                                        'gagal'               => 'Gagal',
                                        default               => $payStatus
                                    };
                                @endphp
                                <span class="badge {{ $payColor }}">{{ $payLabel }}</span>
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center">
                                {{-- LINK KE DETAIL --}}
                                <a href="{{ route('reservasi.admin.show', $item->id) }}" class="btn btn-sm btn-outline-light border-0" title="Lihat Detail">
                                    <span class="material-symbols-outlined text-gold">visibility</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <span class="material-symbols-outlined fs-1 mb-2 opacity-50">folder_open</span>
                                <p class="mb-0">Belum ada data reservasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- PAGINATION --}}
    <div class="d-flex justify-content-end mt-4">
        <nav>
            @if($data->hasPages())
                <ul class="pagination pagination-sm">
                    @if ($data->onFirstPage())
                        <li class="page-item disabled"><span class="page-link bg-transparent border-secondary text-muted">&laquo;</span></li>
                    @else
                        <li class="page-item">
                            <a class="page-link bg-transparent border-secondary text-muted" 
                               href="{{ $data->appends(request()->all())->previousPageUrl() }}">&laquo;</a>
                        </li>
                    @endif

                    <li class="page-item active"><span class="page-link bg-gold border-gold text-dark fw-bold">{{ $data->currentPage() }}</span></li>

                    @if ($data->hasMorePages())
                        <li class="page-item">
                            <a class="page-link bg-transparent border-secondary text-muted" 
                               href="{{ $data->appends(request()->all())->nextPageUrl() }}">&raquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled"><span class="page-link bg-transparent border-secondary text-muted">&raquo;</span></li>
                    @endif
                </ul>
            @endif
        </nav>
    </div>

</div>
@endsection