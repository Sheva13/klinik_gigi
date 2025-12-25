@extends('layouts.adminlte')

@section('title', 'Manajemen Antrian Pasien')

@section('content')

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
                <div class="stat-value text-warning">{{ $stats['menunggu'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">stethoscope</span>
                <div class="stat-label">Sedang Diperiksa</div>
                <div class="stat-value text-info">{{ $stats['diproses'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">check_circle</span>
                <div class="stat-label">Selesai</div>
                <div class="stat-value text-success">{{ $stats['selesai'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">cancel</span>
                <div class="stat-label">Batal</div>
                <div class="stat-value text-danger">{{ $stats['batal'] ?? 0 }}</div>
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