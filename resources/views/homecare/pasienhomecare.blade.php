@extends('layouts.adminlte')

@section('title', 'Manajemen Antrian Home Care')

@section('content')

<div class="container-fluid px-0">

    {{-- 2. HEADER HALAMAN --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div class="d-flex align-items-center gap-3">
            {{-- 🔥 TOMBOL KEMBALI KE INDEX --}}
            <a href="{{ route('homecare.index') }}" class="btn btn-dark border-secondary d-flex align-items-center p-2 rounded-circle me-2">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Manajemen <span class="text-gold">Antrian Home Care</span></h1>
                {{-- Menampilkan Tanggal yang Dipilih --}}
                <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($tanggalPilih)->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                <div class="fw-bold text-white">{{ Auth::user()->nama ?? 'Admin' }}</div>
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
                <div class="stat-label">Menunggu / OTW</div>
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
            <h4 class="fw-bold text-white mb-0">Daftar Kunjungan</h4>

            {{-- 🔥 FORM FILTER (TANGGAL & SEARCH) --}}
            <form action="{{ route('homecare.antrian') }}" method="GET" class="d-flex gap-3">

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
                        <th class="ps-4">NO. PEMERIKSAAN</th>
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
                                <div class="fw-bold text-warning font-monospace">{{ $item->no_pemeriksaan }}</div>
                                @if(isset($item->no_antrian))
                                    <small class="text-gold">Antrian #{{ $item->no_antrian }}</small>
                                @endif
                            </td>

                            <td>
                                <div class="fw-bold text-white">{{ $item->nama_pasien ?? 'Tanpa Nama' }}</div>
                                <small class="text-muted">{{ $item->no_rm ?? '-' }}</small>
                            </td>

                            <td class="text-muted">
                                {{ $item->nama_dokter ?? '-' }}
                            </td>

                            <td>
                                <span class="text-white">{{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }}</span>
                                <small class="text-muted">WIB</small>
                            </td>

                            <td class="text-center">
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
                                <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                            </td>

                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted p-0 text-decoration-none" type="button" data-bs-toggle="dropdown">
                                        <span class="material-symbols-outlined">more_vert</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark border-secondary shadow">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('homecare.show', $item->id) }}">
                                                <span class="material-symbols-outlined fs-6 align-middle me-2 text-gold">visibility</span>
                                                Detail
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
                                <p class="mb-0">Tidak ada kunjungan home care untuk tanggal ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
