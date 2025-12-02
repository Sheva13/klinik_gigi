@extends('layouts.adminlte')

@section('title', 'Detail Reservasi ' . ($reservasi->pasien->nama_lengkap ?? ''))

{{-- 1. CUSTOM CSS DAN ADMINLTE OVERRIDES --}}
@section('adminlte_css')
    {{-- Wajib: Link ke Material Symbols (Google Fonts) --}}
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
    
        /* --- DEFINISI VARIABEL WARNA --- */
        :root {
            --gold-primary: #C1A263;
            --bg-dark: #1E1E1E;
            --card-bg: #2A2A2A;
            --border-color: #2E3035;
        }

        /* 0. ADMINLTE THEME OVERRIDES */
        /* Memaksa elemen AdminLTE menggunakan warna gelap */
        .dark-mode .content-wrapper,
        .dark-mode .main-header,
        .dark-mode .main-sidebar,
        .dark-mode .main-footer {
            background-color: var(--bg-dark) !important; 
            color: #E0E0E0 !important;
        }

        /* Card Style ala Tailwind 'surface-dark' */
        .card-gold-theme {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1rem; /* Rounded Large */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 1.5rem;
        }

        .card-header-gold {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .card-body-gold {
            padding: 1.5rem;
        }

        /* Typography Colors */
        .text-gold {
            color: var(--gold-primary) !important;
        }
        
        .text-gold-bright {
            color: #FFD700 !important;
        }

        .text-muted-custom {
            color: #9A9A9A !important;
            font-size: 0.75rem; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .text-value {
            color: #FFFFFF;
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Form Controls */
        .form-select.bg-dark, .form-control.bg-dark {
            background-color: #121212 !important; 
            color: #E0E0E0 !important;
            border-color: var(--border-color) !important;
        }

        /* Buttons (Gold dan Red Soft) */
        .btn-gold-soft {
            background-color: var(--gold-primary);
            color: #1E1E1E;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-weight: 600;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-gold-soft:hover {
            background-color: #D4B675;
            color: #1E1E1E;
        }

        .btn-red-soft {
            background-color: #F87171;
            color: #1E1E1E;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-weight: 600;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-red-soft:hover {
            background-color: #FCA5A5;
            color: #1E1E1E;
        }

        /* Antrian Box */
        .antrian-box {
            text-align: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }
    </style>
    @parent
@stop

@section('content')
<div class="container-fluid py-4">

    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('reservasi.admin.index') }}" class="btn btn-outline-secondary p-0 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 50%; border-color: #444;">
                    <span class="material-symbols-outlined text-gold" style="font-size: 20px;">arrow_back</span>
                </a>
                <h1 class="h3 fw-bold text-white mb-0">Detail Reservasi Pasien</h1>
            </div>
            <p class="text-muted ms-5 mt-1 mb-0">Detail untuk reservasi **{{ $reservasi->pasien->nama_lengkap ?? 'Pasien Tidak Dikenal' }}**</p>
        </div>

        {{-- Admin Profile Snippet --}}
        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <p class="text-white fw-medium mb-0">{{ Auth::user()->name ?? 'Administrator' }}</p>
                <small class="text-gold">Admin Staff</small>
            </div>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}" 
                alt="Admin" 
                class="rounded-circle object-fit-cover" 
                style="width: 45px; height: 45px; border: 2px solid #2E3035;">
        </div>
    </div>

    <div class="row g-4">
        
        {{-- LEFT COLUMN (Data Pasien & Pembayaran) --}}
        <div class="col-lg-8">
            
            {{-- Card: Data Pasien --}}
            <div class="card-gold-theme">
                <div class="card-header-gold">
                    <h5 class="mb-0 fw-bold text-gold">Data Pasien</h5>
                </div>
                <div class="card-body-gold">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <p class="text-muted-custom">Nama Lengkap</p>
                            <p class="text-value">{{ $reservasi->pasien->nama_lengkap ?? $reservasi->nama_pasien_cadangan ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted-custom">No. HP</p>
                            <p class="text-value">{{ $reservasi->pasien->no_hp ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted-custom">Tempat, Tanggal Lahir</p>
                            <p class="text-value">
                                {{ $reservasi->pasien->tempat_lahir ?? 'Semarang' }}, 
                                {{ isset($reservasi->pasien->tanggal_lahir) ? \Carbon\Carbon::parse($reservasi->pasien->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted-custom">Jenis Pasien</p>
                            <p class="text-value">{{ $reservasi->jenis_pasien ?? 'Umum' }}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted-custom">Alamat</p>
                            <p class="text-value">{{ $reservasi->pasien->alamat_lengkap ?? $reservasi->alamat_lengkap ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Pembayaran --}}
            <div class="card-gold-theme">
                <div class="card-header-gold">
                    <h5 class="mb-0 fw-bold text-gold">Pembayaran & Penagihan</h5>
                </div>
                <div class="card-body-gold">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <p class="text-muted-custom">Metode Pembayaran</p>
                            <p class="text-value text-capitalize">{{ str_replace('_', ' ', $reservasi->metode_pembayaran ?? '-') }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted-custom">Status Pembayaran</p>
                            @if(($reservasi->status_pembayaran ?? '') == 'terverifikasi')
                                <p class="text-success fw-bold mb-0">Lunas</p>
                            @else
                                <p class="text-warning fw-bold mb-0">{{ ucfirst($reservasi->status_pembayaran ?? 'Belum Ada Data') }}</p>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted-custom">Total Biaya</p>
                            <p class="text-value fs-5">Rp {{ number_format($reservasi->pembayaran_total ?? 0, 0, ',', '.') }}</p>
                        </div>
                        
                        {{-- Tombol Verifikasi (Hanya muncul jika belum lunas) --}}
                        @if(($reservasi->status_pembayaran ?? '') != 'terverifikasi' && ($reservasi->status_pembayaran ?? '') != 'gagal')
                        <div class="col-12 mt-2">
                             <form action="{{ route('reservasi.admin.verifyPayment', $reservasi->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100 d-flex align-items-center justify-content-center gap-2 py-2" onclick="return confirm('Verifikasi pembayaran ini?');">
                                    <span class="material-symbols-outlined" style="font-size:18px;">verified</span>
                                    Verifikasi Pembayaran Manual
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN (Detail Janji Temu) --}}
        <div class="col-lg-4">
            <div class="card-gold-theme h-100 d-flex flex-column">
                <div class="card-body-gold flex-grow-1">
                    <h5 class="fw-bold text-gold mb-4">Detail Janji Temu</h5>

                    {{-- No Antrian Besar --}}
                    <div class="antrian-box">
                        <p class="text-muted-custom mb-1">No. Pemeriksaan</p>
                        <h2 class="display-4 fw-bold text-gold-bright mb-0">
                            {{ substr($reservasi->no_pemeriksaan ?? '---', -4) }}
                        </h2>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div>
                            <p class="text-muted-custom">Poli</p>
                            <p class="text-value">{{ $reservasi->dokter->masterPoli->nama_poli ?? 'Umum' }}</p>
                        </div>
                        <div>
                            <p class="text-muted-custom">Dokter</p>
                            <p class="text-value">{{ $reservasi->dokter->nama ?? 'Tidak Ditemukan' }}</p>
                        </div>
                        <div>
                            <p class="text-muted-custom">Tanggal & Waktu</p>
                            <p class="text-value">
                                {{ \Carbon\Carbon::parse($reservasi->tanggal_pesan ?? now())->translatedFormat('d F Y') }} <br>
                                <span class="text-gold">{{ $reservasi->jam_mulai ?? '00:00' }} - {{ $reservasi->jam_selesai ?? '00:00' }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-muted-custom">Keluhan</p>
                            <p class="text-value fst-italic">"{{ $reservasi->keluhan ?? 'Tidak ada keluhan spesifik' }}"</p>
                        </div>
                        <div>
                            <p class="text-muted-custom">Status Saat Ini</p>
                            @php
                                $resStatus = $reservasi->status_reservasi ?? 'menunggu';
                                $badgeClass = match($resStatus) {
                                    'selesai' => 'bg-success',
                                    'batal' => 'bg-danger',
                                    'confirmed' => 'bg-primary',
                                    default => 'bg-warning text-dark'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-3 py-2">
                                {{ strtoupper($resStatus) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="p-4 border-top border-secondary">
                    <div class="d-flex flex-column gap-3">
                        
                        {{-- Form Update Status (Selesai/Confirmed/Menunggu) --}}
                        <form action="{{ route('reservasi.admin.status', $reservasi->id) }}" method="POST">
                            @csrf
                            <div class="input-group mb-3">
                                <select name="status_reservasi" class="form-select bg-dark text-white border-secondary">
                                    <option value="menunggu" {{ ($reservasi->status_reservasi ?? '') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                    <option value="confirmed" {{ ($reservasi->status_reservasi ?? '') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="selesai" {{ ($reservasi->status_reservasi ?? '') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                                <button class="btn btn-warning" type="submit">Update Status</button>
                            </div>
                        </form>

                        {{-- Tombol Navigasi --}}
                        <a href="{{ route('reservasi.admin.create') }}" class="btn btn-warning py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined">add_circle</span>
                            Buat Reservasi Baru
                        </a>

                        <a href="{{ route('reservasi.admin.index') }}" class="btn btn-outline-secondary py-2 fw-bold d-flex align-items-center justify-content-center gap-2" style="color: #9A9A9A; border-color: #444;">
                            <span class="material-symbols-outlined">list</span>
                            Lihat Daftar Semua Reservasi
                        </a>
                        
                        {{-- Tombol Ubah Jadwal (Mengarah ke route edit) --}}
                        <a href="{{ route('reservasi.admin.edit', $reservasi->id) }}" class="btn-gold-soft">
                            <span class="material-symbols-outlined">edit_calendar</span>
                            Ubah Jadwal
                        </a>

                        {{-- Tombol Batalkan Reservasi --}}
                        @if(($reservasi->status_reservasi ?? '') != 'batal')
                        <form action="{{ route('reservasi.admin.status', $reservasi->id) }}" method="POST" onsubmit="return confirm('Batalkan reservasi ini?');">
                            @csrf
                            <input type="hidden" name="status_reservasi" value="batal">
                            <button type="submit" class="btn-red-soft">
                                <span class="material-symbols-outlined">cancel</span>
                                Batalkan Reservasi
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

{{-- 2. Wajib: Aktifkan Dark Mode AdminLTE via JS --}}
@section('adminlte_js')
    @parent
    <script>
        $(document).ready(function() {
            // Memaksa body untuk mengaktifkan class dark-mode agar CSS override bekerja
            $('body').addClass('dark-mode');
        });
    </script>
@stop