@extends('layouts.adminlte')

@section('title', 'Daftar Reservasi Home Care')

@section('styles')
<style>
    /* --- STYLE TABEL DARK MODE YANG KUAT --- */
    .table-dark-custom {
        --bs-table-bg: #1A1A1A;
        --bs-table-color: #ffffff;
        --bs-table-hover-bg: #252525;
        --bs-table-hover-color: #ffffff;
        --bs-table-border-color: #333333;
        
        background-color: #1A1A1A;
        color: #ffffff;
        border-color: #333333;
    }
    
    .table-dark-custom th {
        background-color: #2C2C2C !important;
        color: #f5c542; /* Warna Emas */
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #444;
        padding: 1rem;
    }

    .table-dark-custom td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #333;
    }

    .card-dark {
        background-color: #1A1A1A;
        border: 1px solid #333;
        border-radius: 12px;
        overflow: hidden;
    }

    /* --- FORM INPUTS --- */
    .form-control-dark {
        background-color: #2C2C2C;
        border: 1px solid #4b5563;
        color: #ffffff !important;
    }
    .form-control-dark::placeholder {
        color: #adb5bd !important;
        opacity: 1;
    }
    .form-control-dark[type="date"] {
        color-scheme: dark;
    }
    .form-control-dark:focus {
        background-color: #2C2C2C;
        color: #ffffff;
        border-color: #f5c542;
        box-shadow: 0 0 0 0.2rem rgba(245, 197, 66, 0.25);
    }
    .form-select.form-control-dark {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }

    .badge-status { 
        font-size: 0.8rem; 
        padding: 6px 12px; 
        border-radius: 6px; 
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    /* --- PERBAIKAN PAGINASI (FIX TEXT COLOR) --- */
    /* Mengubah warna teks 'Showing x results' menjadi terang */
    .card-footer .text-muted {
        color: #d1d5db !important; /* Abu-abu terang */
    }
    
    /* Styling Tombol Paginasi Dark Mode */
    .page-link {
        background-color: #2C2C2C;
        border-color: #444;
        color: #f5c542; /* Text Emas */
    }
    .page-link:hover {
        background-color: #333;
        color: #fff;
        border-color: #f5c542;
    }
    .page-item.active .page-link {
        background-color: #f5c542;
        border-color: #f5c542;
        color: #121212;
        font-weight: bold;
    }
    .page-item.disabled .page-link {
        background-color: #1A1A1A;
        border-color: #333;
        color: #555;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-end mb-4 pt-3">
        <div>
            <h1 class="fw-bold text-white mb-1" style="font-size: 1.75rem;">Reservasi Home Care</h1>
            <p class="text-secondary mb-0">Pantau dan kelola pesanan kunjungan dokter ke rumah.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
             {{-- 🔥 TOMBOL LIHAT ANTRIAN (Route akan dibuat) --}}
             <a href="{{ route('homecare.antrian') }}" class="btn btn-outline-light d-flex align-items-center gap-2 border-secondary">
                <span class="material-symbols-outlined text-gold">groups</span>
                Lihat Antrian
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success text-white border-0 mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    {{-- DASHBOARD STATS (MATCHING RESERVASI STYLE) --}}
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

        {{-- Diproses --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">stethoscope</span>
                <div class="stat-label">Diproses</div>
                <div class="stat-value text-info">{{ $stats['diproses'] }}</div>
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
    {{-- Filter Card --}}
    <div class="card card-dark p-4 mb-4 shadow-sm">
        <form action="{{ route('homecare.index') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="text-secondary small mb-1">Pencarian</label>
                    <input type="text" name="search" class="form-control form-control-dark" 
                           placeholder="Cari No Pemeriksaan / Nama Pasien..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="text-secondary small mb-1">Status</label>
                    <select name="status" class="form-select form-control-dark">
                        <option value="">- Semua Status -</option>
                        <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="batal" {{ request('status') == 'batal' ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="text-secondary small mb-1">Tanggal Kunjungan</label>
                    <input type="date" name="start_date" class="form-control form-control-dark" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2 d-grid align-items-end">
                    <button type="submit" class="btn btn-warning fw-bold text-dark" style="background-color: #f5c542; border: none;">
                        <i class="fas fa-filter me-1"></i> Terapkan
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Table Section --}}
    <div class="card card-dark shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark-custom table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">No. Pemeriksaan</th>
                            <th>No. RM</th>
                            <th>Waktu & Antrian</th>
                            <th>Pasien</th>
                            <th>Dokter</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $item)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-warning" style="font-family: monospace; font-size: 1rem;">
                                    {{ $item->no_pemeriksaan }}
                                </span>
                                <div class="text-secondary small mt-1">
                                    <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary font-monospace">{{ $item->no_rm ?? $item->pasien_id }}</span>
                            </td>
                            <td>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($item->tanggal_pesan)->format('d M Y') }}</div>
                                <div class="small text-secondary">
                                    {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                                </div>
                                @if(isset($item->no_antrian) && $item->no_antrian > 0)
                                    <span class="badge bg-gold text-dark mt-1">Antrian #{{ $item->no_antrian }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-white">{{ $item->nama_pasien ?? $item->nama_user ?? 'Nama Tidak Diketahui' }}</div>
                                <div class="text-secondary small mt-1">
                                    <i class="fas fa-phone-alt me-1"></i> {{ $item->no_hp_pasien ?? '-' }}
                                </div>
                            </td>
                            <td>
                                @if($item->nama_dokter)
                                    <div class="fw-bold text-white">{{ $item->nama_dokter }}</div>
                                    <div class="text-secondary small">Dokter</div>
                                @elseif($item->dokter_id)
                                    <div class="fw-bold text-warning">{{ $item->dokter_id }}</div>
                                    <div class="text-secondary small">Nama Tidak Ditemukan</div>
                                @else
                                    <span class="badge bg-danger">Belum Assign</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $s = $item->status_reservasi;
                                    $statusInfo = match(true) {
                                        in_array($s, ['menunggu', 'menunggu_konfirmasi', 'menunggu_pembayaran']) => ['label' => 'Menunggu', 'class' => 'bg-warning-soft text-dark'],
                                        in_array($s, ['dokter_menuju_lokasi', 'sedang_diperiksa', 'dalam_pemeriksaan']) => ['label' => 'Diproses', 'class' => 'bg-info-soft text-dark'],
                                        in_array($s, ['selesai', 'lunas', 'menunggu_pelunasan']) => ['label' => 'Selesai', 'class' => 'bg-success-soft'],
                                        in_array($s, ['dibatalkan', 'gagal', 'expired', 'batal']) => ['label' => 'Batal', 'class' => 'bg-danger-soft'],
                                        default => ['label' => 'Unk.', 'class' => 'bg-secondary']
                                    };
                                @endphp
                                <span class="badge {{ $statusInfo['class'] }} badge-status">
                                    {{ $statusInfo['label'] }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('homecare.show', $item->id) }}" class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-1" style="border-color: #444;">
                                    Detail <i class="fas fa-chevron-right" style="font-size: 0.75rem;"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-25"></i>
                                    <p class="text-secondary mb-0">Tidak ada data reservasi ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- Pagination --}}
        @if($riwayat->hasPages())
        <div class="card-footer bg-transparent border-top border-secondary py-3">
            {{ $riwayat->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection