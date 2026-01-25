@extends('layouts.adminlte')

@section('title', 'Data Reservasi')

@section('content')

<div class="container-fluid px-0">

    {{-- 1. HEADER HALAMAN --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Selamat Datang, <span class="text-gold">Admin!</span></h1>
            <p class="text-muted mb-0">Manajemen data reservasi pasien hari ini.</p>
        </div>
        <div class="d-flex align-items-center gap-3">

            {{-- 🔥 TOMBOL LIHAT ANTRIAN (Route sudah benar) --}}
            <a href="{{ route('reservasi.admin.antrian') }}" class="btn btn-outline-light d-flex align-items-center gap-2 border-secondary">
                <span class="material-symbols-outlined text-gold">groups</span>
                Lihat Antrian
            </a>

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

    {{-- 2. INFO CARDS (UPDATED - 5 Columns) --}}
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

        {{-- 🔥 DIPROSES --}}
        <div class="col-12 col-sm-6 col-md-4 col-xl">
            <div class="stat-card">
                <span class="material-symbols-outlined stat-icon">stethoscope</span>
                <div class="stat-label">Diproses</div>
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
                <div class="col-md-3"> {{-- Lebar kolom disesuaikan karena tombol + hilang --}}
                    <label class="small text-muted mb-2">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-select form-select-dark" onchange="this.form.submit()">
                        <option value="semua" {{ request('status_pembayaran') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Lunas (Online)</option>
                        <option value="terverifikasi" {{ request('status_pembayaran') == 'terverifikasi' ? 'selected' : '' }}>Terverifikasi (Manual)</option>
                        <option value="menunggu_pembayaran" {{ request('status_pembayaran') == 'menunggu_pembayaran' ? 'selected' : '' }}>Belum Bayar (Online)</option>
                        <option value="menunggu_verifikasi" {{ request('status_pembayaran') == 'menunggu_verifikasi' ? 'selected' : '' }}>Cek Bukti (Manual)</option>
                        <option value="gagal" {{ request('status_pembayaran') == 'gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                </div>

                {{-- TOMBOL TAMBAH DINONAKTIFKAN TANPA MENGUBAH KODE ASLI --}}
                {{-- 
                <div class="col-md-1 d-grid">
                    <label class="small text-muted mb-2 d-none d-md-block">&nbsp;</label>
                    <a href="{{ route('reservasi.admin.create') }}" class="btn btn-gold" title="Tambah Reservasi">
                        <span class="material-symbols-outlined">add</span>
                    </a>
                </div> 
                --}}
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
                            <td>
                                <div class="fw-bold">{{ $item->rekamMedis->rekam_medis ?? '-' }}</div>
                                <small class="text-muted">{{ $item->no_pemeriksaan ?? 'Belum Ada' }}</small>
                            </td>
                            <td>{{ $item->rekamMedis->nama ?? 'Data Pasien Hilang' }}</td>
                            <td>
                                <div class="text-gold">{{ $item->dokter->masterPoli->nama_poli ?? '-' }}</div>
                                <small class="text-muted">{{ $item->dokter->nama ?? '-' }}</small>
                            </td>
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
                            <td class="text-center">
                                @php
                                    $s = $item->status_reservasi;
                                    $cls = match($s) {
                                        'menunggu'      => 'bg-warning-soft text-dark',
                                        'dalam_proses'  => 'bg-info-soft text-dark',
                                        'selesai'       => 'bg-success-soft',
                                        'batal'         => 'bg-danger-soft',
                                        default         => 'bg-secondary'
                                    };
                                    $lbl = match($s) {
                                        'dalam_proses' => 'Diproses',
                                        default        => ucfirst($s)
                                    };
                                @endphp
                                <span class="badge {{ $cls }}">{{ $lbl }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $p = $item->status_pembayaran;
                                    $pcl = match($p) {
                                        'lunas', 'terverifikasi' => 'bg-success-soft',
                                        'menunggu_pembayaran'    => 'bg-secondary',
                                        'menunggu_verifikasi'    => 'bg-warning-soft text-dark',
                                        'gagal'                  => 'bg-danger-soft',
                                        default                  => 'bg-secondary'
                                    };
                                    $plb = match($p) {
                                        'lunas'               => 'Lunas (Online)',
                                        'terverifikasi'       => 'Lunas (Manual)',
                                        'menunggu_pembayaran' => 'Belum Bayar',
                                        'menunggu_verifikasi' => 'Cek Bukti',
                                        'gagal'               => 'Gagal',
                                        default               => $p
                                    };
                                @endphp
                                <span class="badge {{ $pcl }}">{{ $plb }}</span>
                            </td>
                            <td class="text-center">
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

    {{-- Pagination --}}
    <div class="d-flex justify-content-end mt-4">
        {{ $data->appends(request()->all())->links() }}
    </div>

</div>
@endsection