@extends('layouts.adminlte') {{-- Menggunakan layout yang sama dengan kamu --}}

@section('title', 'Manajemen Antrian Pasien')

{{-- Jika CSS belum di-load secara global, tempelkan di sini --}}
{{-- @section('head')
<style>
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --bg-dark: #121212;
        --card-bg: #1A1A1A;
        --border-color: #333333;
        --text-muted: #a0a0a0;
        --input-bg: #121212;
    }
    .text-gold { color: var(--gold-primary) !important; }
    .text-muted { color: var(--text-muted) !important; }
    .bg-card { background-color: var(--card-bg) !important; }
    .input-group-text-dark {
        background-color: var(--input-bg);
        border-color: var(--border-color);
        color: var(--text-muted);
    }
    .form-control-dark, .form-select-dark {
        background-color: var(--input-bg);
        border: 1px solid var(--border-color);
        color: #E0E0E0;
        border-radius: 8px;
    }
    .form-control-dark:focus, .form-select-dark:focus {
        border-color: var(--gold-primary);
        box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25); /* Efek fokus emas */
    }
    .border-secondary { border-color: var(--border-color) !important; }
    .btn-gold {
        background-color: var(--gold-primary);
        color: #000;
        font-weight: 700;
        border: none;
        transition: all 0.2s;
    }
    .btn-gold:hover { background-color: var(--gold-hover); color: #000; }
    .rounded-12 { border-radius: 12px; }

    .status-badge {
        font-size: 0.75rem;
        padding: .35em .65em;
        border-radius: .3rem;
        font-weight: 600;
    }
    .status-menunggu { background-color: #D4AF37; color: #000; }
    .status-diperiksa { background-color: #007bff; color: #fff; } /* Biru */
    .status-selesai { background-color: #28a745; color: #fff; } /* Hijau */
    .status-dibatalkan { background-color: #dc3545; color: #fff; } /* Merah */
</style>
@endsection --}}

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Manajemen <span class="text-gold">Antrian Pasien</span></h1>
            <p class="text-muted mb-0">Selasa 25 November 2025</p>
        </div>

        {{-- Profil Admin (Seperti di file Ubah Jadwal) --}}
        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <div class="fw-bold text-white">Basudewa</div>
                <small class="text-muted">Administrator</small>
            </div>
            <img src="{{ asset('assets/images/profile/basudewa.jpg') }}"
                alt="Profile"
                class="rounded-circle border border-secondary"
                style="width: 45px; height: 45px; object-fit: cover;">
        </div>
    </div>

    <div class="row">
        {{-- Kolom Kiri: Form & Ringkasan --}}
        <div class="col-lg-4 mb-4">

            {{-- Buat Nomor Antrian (Form) --}}
            <div class="card bg-card border-secondary rounded-12 mb-4">
                <div class="card-body">
                    <h2 class="fw-bold mb-3" style="font-size: 1.35rem;">Buat nomor Antrian</h2>

                    <div class="mb-3">
                        <label for="search-pasien" class="form-label">Nama atau ID Pasien</label>
                        <div class="input-group">
                            <span class="input-group-text input-group-text-dark">
                                <span class="material-symbols-outlined fs-6">search</span>
                            </span>
                            <input type="text" id="search-pasien" placeholder="Cari Pasien" class="form-control form-control-dark" style="border-left:none;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="pilih-dokter" class="form-label">Dokter atau Departemen</label>
                        <select id="pilih-dokter" class="form-select form-select-dark">
                            <option>Pilih Dokter</option>
                            <option>Dr. Veda Sahasika</option>
                            {{-- Data dokter dari Blade loop jika ada --}}
                        </select>
                    </div>

                    <button class="btn btn-gold w-100 py-2">
                        <span class="material-symbols-outlined fs-6">add</span>
                        Buat & Tambah ke Antrian
                    </button>
                </div>
            </div>

            {{-- Ringkasan Antrian --}}
            <div class="card bg-card border-secondary rounded-12">
                <div class="card-body">
                    <h2 class="fw-bold mb-3" style="font-size: 1.35rem;">Ringkasan Antrian</h2>

                    {{-- Card Menunggu (Kuning Emas) --}}
                    <div class="bg-warning text-dark p-3 rounded-8 mb-3" style="background-color: #D4AF3770 !important; border: 1px solid #D4AF37;">
                        <p class="mb-0 text-white" style="font-size: 0.9rem;">Menunggu</p>
                        <p class="fs-4 fw-bold mb-0 text-white">12</p>
                    </div>

                    {{-- Card Sedang Diperiksa (Biru Langit) --}}
                    <div class="bg-info text-white p-3 rounded-8 mb-3" style="background-color: #007bff70 !important; border: 1px solid #007bff;">
                        <p class="mb-0 text-white" style="font-size: 0.9rem;">Sedang Diperiksa</p>
                        <p class="fs-4 fw-bold mb-0 text-white">3</p>
                    </div>

                    {{-- Card Selesai (Hijau) --}}
                    <div class="bg-success text-white p-3 rounded-8" style="background-color: #28a74570 !important; border: 1px solid #28a745;">
                        <p class="mb-0 text-white" style="font-size: 0.9rem;">Selesai</p>
                        <p class="fs-4 fw-bold mb-0 text-white">8</p>
                    </div>

                </div>
            </div>

        </div>

        {{-- Kolom Kanan: Daftar Antrian --}}
        <div class="col-lg-8">
            <div class="card bg-card border-secondary rounded-12">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold mb-0" style="font-size: 1.5rem;">Daftar Antrian</h2>
                        {{-- Filter --}}
                        <div class="input-group w-auto" style="max-width: 300px;">
                            <span class="input-group-text input-group-text-dark">
                                <span class="material-symbols-outlined fs-6">search</span>
                            </span>
                            <input type="text" placeholder="Filter nama atau #" class="form-control form-control-dark" style="border-left:none;">
                        </div>
                    </div>

                    {{-- Tabel Antrian --}}
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover" style="color: #E0E0E0;">
                            <thead>
                                <tr class="bg-dark" style="background-color: #333333 !important;">
                                    <th class="text-gold">NO. ANTRIAN</th>
                                    <th>NAMA PASIEN</th>
                                    <th>DOKTER</th>
                                    <th>WAKTU DATANG</th>
                                    <th>STATUS</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $queues = [
                                        ['no' => 'RM001', 'name' => 'Farel Sheva Basudewa', 'doctor' => 'Dr. Veda Sahasika', 'time' => '09.15 AM', 'status' => 'Menunggu'],
                                        ['no' => 'RM002', 'name' => 'Wika Dwi Aprilia', 'doctor' => 'Dr. Veda Sahasika', 'time' => '10.30 AM', 'status' => 'Sedang Diperiksa'],
                                        ['no' => 'RM003', 'name' => 'Atha Dhiyahul Fauziyah', 'doctor' => 'Dr. Veda Sahasika', 'time' => '01.15 PM', 'status' => 'Selesai'],
                                        ['no' => 'RM004', 'name' => 'Salma Nurul Fauziyah', 'doctor' => 'Dr. Veda Sahasika', 'time' => '05.30 PM', 'status' => 'Dibatalkan'],
                                        ['no' => 'RM005', 'name' => 'Jeffrey Sugiarto', 'doctor' => 'Dr. Veda Sahasika', 'time' => '08.00 PM', 'status' => 'Menunggu'],
                                    ];
                                @endphp

                                @foreach($queues as $queue)
                                    <tr class="align-middle border-bottom border-secondary" style="border-width: 0.5px !important;">
                                        <td class="text-gold fw-bold">{{ $queue['no'] }}</td>
                                        <td>{{ $queue['name'] }}</td>
                                        <td class="text-muted">{{ $queue['doctor'] }}</td>
                                        <td class="text-muted">{{ $queue['time'] }}</td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'Menunggu' => 'status-menunggu',
                                                    'Sedang Diperiksa' => 'status-diperiksa',
                                                    'Selesai' => 'status-selesai',
                                                    'Dibatalkan' => 'status-dibatalkan',
                                                ][$queue['status']] ?? '';
                                            @endphp
                                            <span class="status-badge {{ $statusClass }}">
                                                {{ $queue['status'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary border-0 text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span class="material-symbols-outlined fs-6">more_vert</span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-dark">
                                                    <li><a class="dropdown-item" href="#">Panggil</a></li>
                                                    <li><a class="dropdown-item" href="#">Ubah Status</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Batalkan</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection