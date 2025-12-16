@extends('layouts.adminlte')

@section('title', 'Manajemen Antrian Pasien')

@section('content')

{{-- 1. STYLE CSS CUSTOM --}}
<style>
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --bg-dark: #121212;
        --card-bg: #1A1A1A;
        --border-color: #333333;
        --text-muted: #a0a0a0;
    }

    h1, h2, h3, h4, h5, h6 { color: #fff !important; }
    .text-gold { color: var(--gold-primary) !important; }
    .text-muted { color: var(--text-muted) !important; }

    .stat-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
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

    .val-warning { color: #ffc107 !important; }
    .val-info { color: #17a2b8 !important; }
    .val-success { color: #28a745 !important; }
    .val-danger { color: #dc3545 !important; }

    .form-control-dark {
        background-color: #121212;
        border: 1px solid var(--border-color);
        color: #E0E0E0;
        border-radius: 8px;
        padding: 0.6rem 1rem;
    }
    .form-control-dark:focus {
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

    .queue-table-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        min-height: 400px;
    }

    .table-dark-custom {
        width: 100%;
        color: #E0E0E0;
        margin-bottom: 0;
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
        white-space: nowrap;
    }
    .table-dark-custom tbody td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.95rem;
        background-color: var(--card-bg) !important;
        color: #E0E0E0 !important;
    }
    .table-dark-custom tr:last-child td { border-bottom: none; }
    .table-dark-custom tbody tr:hover td {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }

    .no-antrian-text {
        font-family: 'Courier New', monospace;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--gold-primary);
        letter-spacing: 1px;
    }

    .badge {
        padding: 0.5em 0.8em;
        font-weight: 600;
        border-radius: 6px;
    }
    .bg-warning-soft { background-color: rgba(255, 193, 7, 0.2); color: #ffc107; border: 1px solid #ffc107; }
    .bg-info-soft { background-color: rgba(23, 162, 184, 0.2); color: #17a2b8; border: 1px solid #17a2b8; }
    .bg-success-soft { background-color: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.2); color: #dc3545; border: 1px solid #dc3545; }

    .btn-link-gold { color: var(--gold-primary); text-decoration: none; }
    .btn-link-gold:hover { color: var(--gold-hover); text-decoration: none; }

    input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
</style>

<div class="container-fluid px-0">

    {{-- 2. HEADER HALAMAN --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div class="d-flex align-items-center gap-3">
            {{-- 🔥 TOMBOL KEMBALI KE INDEX --}}
            <a href="{{ route('reservasi.admin.index') }}" class="btn btn-dark border-secondary d-flex align-items-center p-2 rounded-circle me-2">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Manajemen <span class="text-gold">Antrian</span></h1>
                {{-- Menampilkan Tanggal yang Dipilih --}}
                <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($tanggalPilih)->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <div class="fw-bold text-white">{{ Auth::guard('admin')->user()->nama ?? 'Admin' }}</div>
                <small class="text-muted">Administrator</small>
            </div>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}"
                 alt="Profile"
                 class="rounded-circle border border-secondary"
                 style="width: 45px; height: 45px; object-fit: cover;">
        </div>
    </div>

    {{-- 3. RINGKASAN ANTRIAN (DITARUH DI ATAS) --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">hourglass_top</span>
                <div class="stat-label">Menunggu</div>
                <div class="stat-value val-warning">{{ $stats['menunggu'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">stethoscope</span>
                <div class="stat-label">Sedang Diperiksa</div>
                <div class="stat-value val-info">{{ $stats['diproses'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">check_circle</span>
                <div class="stat-label">Selesai</div>
                <div class="stat-value val-success">{{ $stats['selesai'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">cancel</span>
                <div class="stat-label">Batal</div>
                <div class="stat-value val-danger">{{ $stats['batal'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- 4. DAFTAR ANTRIAN HARI INI (TABLE DI BAWAH) --}}
    <div class="queue-table-card shadow-sm">

        <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-3 border-bottom border-secondary">
            <h4 class="fw-bold text-white mb-0">Daftar Antrian</h4>

            {{-- 🔥 FORM FILTER (TANGGAL & SEARCH) --}}
            <form action="{{ route('reservasi.admin.antrian') }}" method="GET" class="d-flex gap-3">

                {{-- Input Tanggal --}}
                <div class="input-group" style="width: 200px;">
                    <span class="input-group-text input-group-text-dark">
                        <span class="material-symbols-outlined fs-6">calendar_month</span>
                    </span>
                    <input type="date" name="tanggal" value="{{ $tanggalPilih }}"
                           class="form-control form-control-dark"
                           style="border-left:none;"
                           onchange="this.form.submit()">
                </div>

                {{-- Input Search --}}
                <div class="input-group" style="width: 250px;">
                    <button type="submit" class="input-group-text input-group-text-dark btn btn-dark">
                        <span class="material-symbols-outlined fs-5">search</span>
                    </button>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control form-control-dark"
                           style="border-left:none;"
                           placeholder="Cari nama / no antrian...">
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-dark-custom mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">NO. ANTRIAN</th>
                        <th>NAMA PASIEN</th>
                        <th>DOKTER</th>
                        <th>JAM JANJI</th>
                        <th class="text-center">STATUS</th>
                        <th class="text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($antrian as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="no-antrian-text">{{ $item->no_antrian ?? '-' }}</div>
                            </td>

                            <td>
                                <div class="fw-bold text-white">{{ $item->rekamMedis->nama ?? 'Tanpa Nama' }}</div>
                                <small class="text-muted">{{ $item->rekamMedis->rekam_medis ?? '-' }}</small>
                            </td>

                            <td class="text-muted">
                                {{ $item->dokter->nama ?? '-' }}
                            </td>

                            <td>
                                <span class="text-white">{{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }}</span>
                                <small class="text-muted">WIB</small>
                            </td>

                            <td class="text-center">
                                @php
                                    $s = $item->status_reservasi;
                                    $bgClass = match($s) {
                                        'menunggu'     => 'bg-warning-soft',
                                        'dalam_proses' => 'bg-info-soft',
                                        'selesai'      => 'bg-success-soft',
                                        'batal'        => 'bg-danger-soft',
                                        default        => 'bg-secondary'
                                    };
                                    $label = match($s) {
                                        'dalam_proses' => 'Sedang Diperiksa',
                                        'menunggu'     => 'Menunggu',
                                        'selesai'      => 'Selesai',
                                        'batal'        => 'Dibatalkan',
                                        default        => ucfirst($s)
                                    };
                                @endphp
                                <span class="badge {{ $bgClass }}">{{ $label }}</span>
                            </td>

                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" type="button" data-bs-toggle="dropdown">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark border-secondary shadow">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('reservasi.admin.show', $item->id) }}">
                                                <span class="material-symbols-outlined fs-6 align-middle me-2 text-gold">visibility</span>
                                                Detail
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider border-secondary"></li>

                                        @if($item->status_reservasi == 'menunggu')
                                        {{-- Tombol Aksi Cepat Panggil Masuk --}}
                                        <li>
                                            <form action="{{ route('reservasi.admin.status', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status_reservasi" value="dalam_proses">
                                                <button type="submit" class="dropdown-item text-info">
                                                    <span class="material-symbols-outlined fs-6 align-middle me-2">campaign</span>
                                                    Panggil Masuk
                                                </button>
                                            </form>
                                        </li>
                                        @endif

                                        @if($item->status_reservasi == 'dalam_proses')
                                        {{-- Tombol Aksi Cepat Selesai Periksa --}}
                                        <li>
                                            <form action="{{ route('reservasi.admin.status', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status_reservasi" value="selesai">
                                                <button type="submit" class="dropdown-item text-success">
                                                    <span class="material-symbols-outlined fs-6 align-middle me-2">check_circle</span>
                                                    Selesai Periksa
                                                </button>
                                            </form>
                                        </li>
                                        @endif

                                        {{-- Tombol untuk mereset status jika perlu --}}
                                        @if($item->status_reservasi == 'dalam_proses' || $item->status_reservasi == 'selesai')
                                        <li>
                                            <form action="{{ route('reservasi.admin.status', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status_reservasi" value="menunggu">
                                                <button type="submit" class="dropdown-item text-warning">
                                                    <span class="material-symbols-outlined fs-6 align-middle me-2">replay</span>
                                                    Reset Antrian
                                                </button>
                                            </form>
                                        </li>
                                        @endif

                                        <li><hr class="dropdown-divider border-secondary"></li>

                                        {{-- Tombol Edit --}}
                                        <li>
                                            <a class="dropdown-item" href="{{ route('reservasi.admin.edit', $item->id) }}">
                                                <span class="material-symbols-outlined fs-6 align-middle me-2">edit</span>
                                                Edit
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <span class="material-symbols-outlined fs-1 opacity-25 d-block mb-3">groups_3</span>
                                <p class="mb-0">Tidak ada antrian untuk tanggal ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection