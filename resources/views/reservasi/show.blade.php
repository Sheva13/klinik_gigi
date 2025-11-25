@extends('layouts.adminlte')

@section('title', 'Detail Reservasi')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('reservasi.admin.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="h3 fw-bold mb-0">Detail: {{ $reservasi->no_pemeriksaan }}</h1>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4 border-0" style="background-color: #1A1A1A; border: 1px solid #333333;">
                <div class="card-header border-bottom border-secondary py-3">
                    <h5 class="mb-0 text-white">Informasi Medis & Jadwal</h5>
                </div>
                <div class="card-body text-secondary">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase">Pasien</label>
                            <p class="text-white fw-bold fs-5 mb-0">{{ $reservasi->pasien->nama_lengkap ?? '-' }}</p>
                            <small>No RM: {{ $reservasi->pasien_id }}</small>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase">Dokter</label>
                            <p class="text-white fw-bold fs-5 mb-0">{{ $reservasi->dokter->nama ?? '-' }}</p>
                            <small>{{ $reservasi->jadwal->poli->nama_poli ?? '-' }}</small>
                        </div>
                    </div>
                    
                    <hr class="border-secondary">
                    
                    <div class="row">
                        <div class="col-md-4">
                            <label class="small text-muted">Tanggal Kunjungan</label>
                            <p class="text-white">{{ \Carbon\Carbon::parse($reservasi->tanggal_pesan)->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Jam Layanan</label>
                            <p class="text-white">{{ $reservasi->jam_mulai }} - {{ $reservasi->jam_selesai }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Keluhan</label>
                            <p class="text-white">{{ $reservasi->keluhan ?? 'Tidak ada keluhan spesifik' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            
            <div class="card mb-4 border-0" style="background-color: #1A1A1A; border: 1px solid #333333;">
                <div class="card-header border-bottom border-secondary py-3">
                    <h5 class="mb-0 text-white">Status Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Metode</span>
                        <span class="text-white fw-bold">{{ $reservasi->metode_pembayaran }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-secondary">Total Biaya</span>
                        <span class="text-warning fw-bold">Rp {{ number_format($reservasi->pembayaran_total, 0, ',', '.') }}</span>
                    </div>

                    <div class="alert {{ $reservasi->status_pembayaran == 'terverifikasi' ? 'alert-success' : 'alert-warning' }} d-flex align-items-center" role="alert">
                        @if($reservasi->status_pembayaran == 'terverifikasi')
                            <span class="material-symbols-outlined me-2">check_circle</span> LUNAS
                        @else
                            <span class="material-symbols-outlined me-2">pending</span> {{ strtoupper(str_replace('_', ' ', $reservasi->status_pembayaran)) }}
                        @endif
                    </div>

                    @if($reservasi->status_pembayaran != 'terverifikasi')
                    <form action="{{ route('reservasi.admin.verifyPayment', $reservasi->id) }}" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini secara manual?');">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined">verified</span> Verifikasi Manual
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="card border-0" style="background-color: #1A1A1A; border: 1px solid #333333;">
                <div class="card-header border-bottom border-secondary py-3">
                    <h5 class="mb-0 text-white">Update Status Kunjungan</h5>
                </div>
                <div class="card-body">
                    <p class="text-secondary small">Ubah status jika pasien sudah datang atau membatalkan.</p>
                    
                    <form action="{{ route('reservasi.admin.status', $reservasi->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select name="status_reservasi" class="form-select bg-dark text-white border-secondary">
                                <option value="menunggu" {{ $reservasi->status_reservasi == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                <option value="confirmed" {{ $reservasi->status_reservasi == 'confirmed' ? 'selected' : '' }}>Confirmed (Siap)</option>
                                <option value="selesai" {{ $reservasi->status_reservasi == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="batal" {{ $reservasi->status_reservasi == 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection