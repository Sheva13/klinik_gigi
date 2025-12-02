@extends('layouts.adminlte')

@section('title', 'Data Reservasi')

{{-- 1. Tambahkan Material Symbols (Google Fonts) dan custom CSS --}}
@section('adminlte_css')
    {{-- Link ke Material Symbols (Google Fonts) --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        /* Pengaturan umum untuk ikon Material Symbols */
        .material-symbols-outlined {
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }
    </style>
    @parent
@stop

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

    /* 0. ADMINLTE THEME OVERRIDES (Dark Mode) */
    .dark-mode .main-header,
    .dark-mode .main-sidebar,
    .dark-mode .main-footer,
    .dark-mode .content-wrapper,
    .dark-mode .card,
    .dark-mode .modal-content {
        background-color: var(--bg-dark) !important;
        border-color: var(--border-color) !important;
        color: #E0E0E0 !important;
    }

    .dark-mode .nav-link, 
    .dark-mode .brand-link,
    .dark-mode .main-sidebar .nav-sidebar .nav-item .nav-link p,
    .dark-mode .main-sidebar .nav-sidebar .nav-item .nav-link i.nav-icon {
        color: #E0E0E0 !important; /* Warna text sidebar */
    }

    .dark-mode .nav-sidebar .nav-item > .nav-link.active, 
    .dark-mode .nav-sidebar .nav-item > .nav-link.active:hover {
        background-color: var(--gold-primary) !important;
        color: #000 !important;
        font-weight: 600;
    }

    .dark-mode .navbar-nav > .nav-item > .nav-link {
        color: #E0E0E0 !important;
    }

    .dark-mode .content-wrapper {
        color: #E0E0E0 !important;
    }
    
    .dark-mode .table {
        color: #E0E0E0 !important;
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
    
    /* PAGINATION Customization */
    .page-item .page-link {
        color: #E0E0E0;
    }
    .page-item.active .page-link {
        background-color: var(--gold-primary) !important;
        border-color: var(--gold-primary) !important;
        color: #000 !important;
    }
    .page-item:not(.active) .page-link:hover {
        background-color: #252525 !important;
        border-color: var(--border-color) !important;
    }
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
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">calendar_month</span>
                <div class="stat-label">Total Reservasi</div>
                <div class="stat-value text-gold">{{ $stats['total'] }}</div> 
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">hourglass_top</span>
                <div class="stat-label">Pasien Menunggu</div>
                <div class="stat-value text-gold">{{ $stats['menunggu'] }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">check_circle</span>
                <div class="stat-label">Pasien Selesai</div>
                <div class="stat-value text-gold">{{ $stats['selesai'] }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">cancel</span>
                <div class="stat-label">Pasien Batal</div>
                <div class="stat-value text-gold">{{ $stats['batal'] }}</div>
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

                {{-- FILTER STATUS RESERVASI (SESUAI DB) --}}
                <div class="col-md-2">
                    <label class="small text-muted mb-2">Status Reservasi</label>
                    <select name="status_reservasi" class="form-select form-select-dark" onchange="this.form.submit()">
                        <option value="semua" {{ request('status_reservasi') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="menunggu" {{ request('status_reservasi') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="dalam_proses" {{ request('status_reservasi') == 'dalam_proses' ? 'selected' : '' }}>Dalam Proses</option>
                        <option value="selesai" {{ request('status_reservasi') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="batal" {{ request('status_reservasi') == 'batal' ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>

                {{-- FILTER STATUS PEMBAYARAN (SESUAI DB) --}}
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
                                            // Mapping hari jika disimpan sebagai angka
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
                                        'menunggu'      => 'text-warning',
                                        'dalam_proses' => 'text-info',
                                        'selesai'      => 'text-success',
                                        'batal'        => 'text-danger',
                                        default        => 'text-muted'
                                    };
                                    $resLabel = match($resStatus) {
                                        'menunggu'      => 'Menunggu',
                                        'dalam_proses' => 'Diproses',
                                        'selesai'      => 'Selesai',
                                        'batal'        => 'Batal',
                                        default        => $resStatus
                                    };
                                @endphp
                                <span class="fw-bold {{ $resColor }}">{{ $resLabel }}</span>
                            </td>

                            {{-- STATUS PEMBAYARAN (SESUAI DB) --}}
                            <td class="text-center">
                                @php
                                    $payStatus = $item->status_pembayaran;
                                    $payColor = match($payStatus) {
                                        'terverifikasi'       => 'text-success',
                                        'menunggu_pembayaran' => 'text-warning',
                                        'menunggu_verifikasi' => 'text-primary',
                                        'gagal'               => 'text-danger',
                                        default               => 'text-muted'
                                    };
                                    $payLabel = match($payStatus) {
                                        'terverifikasi'       => 'Lunas',
                                        'menunggu_pembayaran' => 'Belum Bayar',
                                        'menunggu_verifikasi' => 'Cek Bukti',
                                        'gagal'               => 'Gagal',
                                        default               => $payStatus
                                    };
                                @endphp
                                <span class="fw-bold {{ $payColor }}">{{ $payLabel }}</span>
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center">
                                <a href="{{ route('reservasi.admin.show', $item->id) }}" class="btn btn-sm btn-outline-light border-0">
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
    
    {{-- Pagination --}}
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

{{-- 2. Tambahkan script untuk mengaktifkan Dark Mode AdminLTE --}}
@section('adminlte_js')
    @parent
    <script>
        $(document).ready(function() {
            // 1. Paksa mode gelap pada body AdminLTE
            $('body').addClass('dark-mode');

            // 2. Set Active link di sidebar untuk konsistensi styling Gold
            // Halaman ini tidak perlu Active Class di AdminLTE karena sudah di render sebagai Content.
            // Namun, jika ada menu di sidebar yang mengarah ke halaman ini, kita pastikan class 'active' terpasang.
            // (Hanya diperlukan jika menu sidebar adalah bagian dari layout AdminLTE yang tidak di-handle otomatis)
            // Contoh sederhana untuk rute reservasi.admin.index:
            // var currentRoute = '{{ route('reservasi.admin.index') }}';
            // $('.nav-sidebar a[href="' + currentRoute + '"]').addClass('active');
        });
    </script>
@stop