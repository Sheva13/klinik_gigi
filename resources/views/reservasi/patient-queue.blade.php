@extends('layouts.adminlte')

@section('title', 'Manajemen Antrian Pasien')

@section('content')

{{-- 1. STYLE DARI INDEX.BLADE (SUPAYA TEMA SENADA) --}}
<style>
    /* --- CONFIG WARNA DARK GOLD (Sama persis dengan index.blade.php) --- */
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --bg-dark: #121212;
        --card-bg: #1A1A1A;
        --border-color: #333333;
        --text-muted: #a0a0a0;
        --input-bg: #121212;
    }

    /* Typography Overrides */
    h1, h2, h3, h4, h5, h6 { color: #fff !important; }
    .text-gold { color: var(--gold-primary) !important; }
    .text-muted { color: var(--text-muted) !important; }
    
    /* CARD STYLE (Menggunakan variabel yang sama dengan Index) */
    .bg-card { 
        background-color: var(--card-bg) !important; 
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* INPUT FIELDS (Agar Senada dengan Index) */
    .input-group-text-dark {
        background-color: var(--input-bg);
        border: 1px solid var(--border-color);
        border-right: none;
        color: var(--text-muted);
    }
    .form-control-dark, .form-select-dark {
        background-color: var(--input-bg);
        border: 1px solid var(--border-color);
        color: #E0E0E0; /* Warna teks input disamakan */
        border-radius: 8px;
        padding: 0.6rem 1rem;
    }
    .form-control-dark:focus, .form-select-dark:focus {
        border-color: var(--gold-primary);
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
        background-color: var(--input-bg);
        color: #fff;
    }
    /* Fix border radius kalau ada input group */
    .input-group > .form-control-dark {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    /* BUTTONS */
    .btn-gold {
        background-color: var(--gold-primary);
        color: #000;
        font-weight: 700;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1rem; /* Sedikit lebih lebar */
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%; /* Full width agar sesuai desain kiri */
    }
    .btn-gold:hover { background-color: var(--gold-hover); color: #000; }

    /* STATUS BADGES (Badge Pill Shape - Sesuai Gambar) */
    .status-badge {
        font-size: 0.75rem;
        padding: .35em .8em;
        border-radius: 50px; /* Pill Shape */
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    /* Dot Indicator (Titik kecil di dalam badge) */
    .dot { height: 6px; width: 6px; border-radius: 50%; display: inline-block; }

    .status-menunggu { background-color: rgba(255, 193, 7, 0.15); color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.3); }
    .dot-menunggu { background-color: #ffc107; }

    .status-diproses { background-color: rgba(23, 162, 184, 0.15); color: #17a2b8; border: 1px solid rgba(23, 162, 184, 0.3); }
    .dot-diproses { background-color: #17a2b8; }

    .status-selesai { background-color: rgba(40, 167, 69, 0.15); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.3); }
    .dot-selesai { background-color: #28a745; }

    .status-batal { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.3); }
    .dot-batal { background-color: #dc3545; }

    /* RINGKASAN BOX (Kotak Kecil di Kiri Bawah) */
    .summary-box {
        background-color: #252525;
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 4px solid transparent; /* Garis warna di kiri */
    }
    .box-warning { border-color: #ffc107; }
    .box-info { border-color: #17a2b8; }
    .box-success { border-color: #28a745; }

    /* TABEL HEADER & ROW */
    .table-responsive { border-radius: 12px; overflow: hidden; }
    .table-header th {
        background-color: #252525 !important; /* Header tabel lebih gelap sedikit */
        color: var(--text-muted);
        font-size: 0.85rem;
        text-transform: uppercase;
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    .queue-row td {
        padding: 1.2rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        color: #E0E0E0;
        background-color: var(--card-bg);
    }
    .queue-row:hover td {
        background-color: rgba(255, 255, 255, 0.03);
    }
    .no-antrian-text {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--gold-primary);
        letter-spacing: 0.5px;
    }
</style>

<div class="container-fluid px-0">

    {{-- HEADER HALAMAN --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Manajemen <span class="text-gold">Antrian Pasien</span></h1>
            {{-- Tanggal Hari Ini Dinamis --}}
            <p class="text-muted mb-0">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                {{-- Nama Admin Dinamis --}}
                <div class="fw-bold text-white">{{ Auth::guard('admin')->user()->nama ?? 'Admin' }}</div>
                <small class="text-muted">Administrator</small>
            </div>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}"
                alt="Profile"
                class="rounded-circle border border-secondary"
                style="width: 45px; height: 45px; object-fit: cover;">
        </div>
    </div>

    <div class="row g-4">
        
        {{-- KOLOM KIRI: FORM INPUT & RINGKASAN --}}
        <div class="col-lg-4">

            {{-- CARD 1: BUAT NOMOR ANTRIAN --}}
            <div class="bg-card mb-4">
                <h4 class="fw-bold mb-3 text-white" style="font-size: 1.25rem;">Buat Nomor Antrian</h4>

                {{-- Form ini akan mengarahkan ke halaman Create Manual --}}
                <form action="{{ route('reservasi.admin.create') }}" method="GET">
                    
                    <div class="mb-3">
                        <label class="text-muted small mb-2">Nama atau ID Pasien</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-dark">
                                <span class="material-symbols-outlined fs-6">search</span>
                            </span>
                            {{-- Input ini cuma pancingan, nanti dioper ke halaman create --}}
                            <input type="text" class="form-control form-control-dark" 
                                   placeholder="Cari Pasien..." readonly 
                                   style="border-left:none; cursor: pointer;"
                                   onclick="this.closest('form').submit();">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small mb-2">Dokter atau Departemen</label>
                        {{-- Disabled karena pilih di halaman selanjutnya --}}
                        <select class="form-select form-select-dark" disabled>
                            <option>Pilih di halaman selanjutnya...</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-gold">
                        <span class="material-symbols-outlined fs-5">add</span>
                        Buat & Tambah ke Antrian
                    </button>
                </form>
            </div>

            {{-- CARD 2: RINGKASAN ANTRIAN (Data Dinamis dari Controller) --}}
            <div class="bg-card">
                <h4 class="fw-bold mb-4 text-white" style="font-size: 1.25rem;">Ringkasan Antrian</h4>

                {{-- Menunggu --}}
                <div class="summary-box box-warning">
                    <div>
                        <div class="text-muted small mb-1">Menunggu</div>
                        <h2 class="fw-bold text-white mb-0">{{ $stats['menunggu'] }}</h2>
                    </div>
                    {{-- Icon Jam Pasir --}}
                    <span class="material-symbols-outlined text-warning fs-2">hourglass_top</span>
                </div>

                {{-- Sedang Diperiksa --}}
                <div class="summary-box box-info">
                    <div>
                        <div class="text-muted small mb-1">Sedang Diperiksa</div>
                        <h2 class="fw-bold text-white mb-0">{{ $stats['diproses'] }}</h2>
                    </div>
                    {{-- Icon Stetoskop --}}
                    <span class="material-symbols-outlined text-info fs-2">stethoscope</span>
                </div>

                {{-- Selesai --}}
                <div class="summary-box box-success">
                    <div>
                        <div class="text-muted small mb-1">Selesai</div>
                        <h2 class="fw-bold text-white mb-0">{{ $stats['selesai'] }}</h2>
                    </div>
                    {{-- Icon Centang --}}
                    <span class="material-symbols-outlined text-success fs-2">check_circle</span>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: DAFTAR ANTRIAN (TABEL) --}}
        <div class="col-lg-8">
            <div class="bg-card h-100" style="padding: 0; overflow: hidden;"> {{-- Padding 0 biar header tabel full --}}
                
                <div class="p-4 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h4 class="fw-bold mb-0 text-white" style="font-size: 1.5rem;">Daftar Antrian Hari Ini</h4>
                    
                    {{-- Filter Search --}}
                    <form action="{{ route('reservasi.admin.antrian') }}" method="GET">
                        <div class="input-group w-auto" style="min-width: 250px;">
                            <span class="input-group-text input-group-text-dark">
                                <span class="material-symbols-outlined fs-6">search</span>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / no antrian..." class="form-control form-control-dark" style="border-left:none;">
                        </div>
                    </form>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr class="table-header">
                                <th class="ps-4" width="15%">NO ANTRIAN</th>
                                <th width="25%">NAMA PASIEN</th>
                                <th width="20%">DOKTER</th>
                                <th width="15%">WAKTU</th>
                                <th width="15%" class="text-center">STATUS</th>
                                <th width="10%" class="text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($antrian as $item)
                                <tr class="queue-row">
                                    {{-- No Antrian --}}
                                    <td class="ps-4">
                                        <div class="no-antrian-text">{{ $item->no_antrian ?? '-' }}</div>
                                    </td>
                                    
                                    {{-- Nama Pasien --}}
                                    <td>
                                        <div class="fw-bold text-white">{{ $item->rekamMedis->nama ?? 'Tanpa Nama' }}</div>
                                        <div class="small text-muted" style="font-size: 0.8rem;">{{ $item->rekamMedis->rekam_medis ?? '-' }}</div>
                                    </td>
                                    
                                    {{-- Dokter --}}
                                    <td class="text-muted small">
                                        {{ $item->dokter->nama ?? '-' }}
                                    </td>
                                    
                                    {{-- Waktu --}}
                                    <td class="text-muted small">
                                        {{ \Carbon\Carbon::parse($item->created_at)->format('h.i A') }}
                                    </td>
                                    
                                    {{-- Status Badge --}}
                                    <td class="text-center">
                                        @php
                                            $s = $item->status_reservasi;
                                            $badgeClass = match($s) {
                                                'menunggu'     => 'status-menunggu',
                                                'dalam_proses' => 'status-diproses',
                                                'selesai'      => 'status-selesai',
                                                'batal'        => 'status-batal',
                                                default        => 'status-menunggu'
                                            };
                                            $dotClass = match($s) {
                                                'menunggu'     => 'dot-menunggu',
                                                'dalam_proses' => 'dot-diproses',
                                                'selesai'      => 'dot-selesai',
                                                'batal'        => 'dot-batal',
                                                default        => 'dot-menunggu'
                                            };
                                            $label = match($s) { 
                                                'dalam_proses' => 'Diperiksa', 
                                                default => ucfirst($s) 
                                            };
                                        @endphp
                                        <span class="status-badge {{ $badgeClass }}">
                                            <span class="dot {{ $dotClass }}"></span> {{ $label }}
                                        </span>
                                    </td>

                                    {{-- Aksi Dropdown --}}
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" style="text-decoration: none;">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-dark border-secondary shadow" style="background-color: #252525;">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('reservasi.admin.show', $item->id) }}">
                                                        <span class="material-symbols-outlined fs-6 align-middle me-2 text-gold">visibility</span> Detail
                                                    </a>
                                                </li>
                                                
                                                {{-- Aksi Cepat: Panggil --}}
                                                @if($item->status_reservasi == 'menunggu')
                                                <li>
                                                    <form action="{{ route('reservasi.admin.status', $item->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status_reservasi" value="dalam_proses">
                                                        <button type="submit" class="dropdown-item text-info">
                                                            <span class="material-symbols-outlined fs-6 align-middle me-2">campaign</span> Panggil Masuk
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif

                                                {{-- Aksi Cepat: Selesai --}}
                                                @if($item->status_reservasi == 'dalam_proses')
                                                <li>
                                                    <form action="{{ route('reservasi.admin.status', $item->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status_reservasi" value="selesai">
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <span class="material-symbols-outlined fs-6 align-middle me-2">check_circle</span> Selesai Periksa
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <span class="material-symbols-outlined fs-1 opacity-25 d-block mb-3">groups_3</span>
                                        <p class="mb-0">Belum ada antrian aktif hari ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection