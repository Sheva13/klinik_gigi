@extends('layouts.adminlte')

@section('title', 'Detail Reservasi')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('reservasi.admin.index') }}" class="btn btn-outline-secondary d-flex align-items-center">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="h3 fw-bold mb-0">Detail: {{ $reservasi->no_pemeriksaan }}</h1>

        <!-- Tombol Edit -->
        <button class="btn btn-warning ms-auto" data-bs-toggle="modal" data-bs-target="#modalEditReservasi">
            <span class="material-symbols-outlined">edit</span> Edit
        </button>
    </div>


    <div class="row">
        <!-- INFORMASI MEDIS -->
        <div class="col-md-8">
            <div class="card mb-4 border-0" style="background-color: #1A1A1A; border: 1px solid #333333;">
                <div class="card-header border-bottom border-secondary py-3">
                    <h5 class="mb-0 text-white">Informasi Medis & Jadwal</h5>
                </div>

                <div class="card-body text-secondary">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase">Pasien</label>

                            <p class="text-white fw-bold fs-5 mb-0">
                                {{ $reservasi->pasien->nama_lengkap ?? '-' }}
                            </p>

                            <small>No RM: {{ $reservasi->pasien->rekam_medis ?? '-' }}</small><br>
                            <small>No Pemeriksaan: {{ $reservasi->no_pemeriksaan }}</small>
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
                            <p class="text-white">
                                {{ \Carbon\Carbon::parse($reservasi->tanggal_pesan)->translatedFormat('l, d F Y') }}
                            </p>
                        </div>

                        <div class="col-md-4">
                            <label class="small text-muted">Jam Layanan</label>
                            <p class="text-white">
                                {{ $reservasi->jam_mulai }} - {{ $reservasi->jam_selesai }}
                            </p>
                        </div>

                        <div class="col-md-4">
                            <label class="small text-muted">Keluhan</label>
                            <p class="text-white">{{ $reservasi->keluhan ?? '-' }}</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- STATUS PEMBAYARAN + RESERVASI -->
        <div class="col-md-4">

            <!-- PEMBAYARAN -->
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
                        <span class="text-warning fw-bold">
                            Rp {{ number_format($reservasi->pembayaran_total, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="alert 
                        {{ $reservasi->status_pembayaran == 'terverifikasi' ? 'alert-success' : 'alert-warning' }}
                        d-flex align-items-center" role="alert">

                        @if($reservasi->status_pembayaran == 'terverifikasi')
                            <span class="material-symbols-outlined me-2">check_circle</span>
                            LUNAS / TERVERIFIKASI
                        @else
                            <span class="material-symbols-outlined me-2">pending</span>
                            {{ strtoupper(str_replace('_', ' ', $reservasi->status_pembayaran)) }}
                        @endif

                    </div>

                    @if($reservasi->status_pembayaran != 'terverifikasi')
                        <button 
                            class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2"
                            data-bs-toggle="modal"
                            data-bs-target="#modalKonfirmasiVerifikasi">
                            <span class="material-symbols-outlined">verified</span>
                            Verifikasi Pembayaran
                        </button>
                    @endif

                </div>
            </div>


            <!-- STATUS RESERVASI -->
            <div class="card border-0" style="background-color: #1A1A1A; border: 1px solid #333333;">
                <div class="card-header border-bottom border-secondary py-3">
                    <h5 class="mb-0 text-white">Update Status Kunjungan</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('reservasi.admin.status', $reservasi->id) }}" 
                          method="POST" 
                          onsubmit="return confirm('Yakin ubah status reservasi?');">
                        @csrf

                        <div class="mb-3">
                            <select name="status_reservasi" 
                                    class="form-select bg-dark text-white border-secondary">
                                <option value="menunggu"  {{ $reservasi->status_reservasi == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                <option value="confirmed" {{ $reservasi->status_reservasi == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="selesai"   {{ $reservasi->status_reservasi == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="batal"     {{ $reservasi->status_reservasi == 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>


<!-- ============================= -->
<!-- MODAL: EDIT RESERVASI -->
<!-- ============================= -->
<div class="modal fade" id="modalEditReservasi" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background-color: #1A1A1A; border:1px solid #444">

            <form action="{{ route('reservasi.admin.update', $reservasi->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white">Edit Reservasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-white">

                    <div class="mb-3">
                        <label class="small text-secondary">Pilih Dokter</label>
                        <select name="dokter_id" class="form-select bg-dark text-white border-secondary">
                            @foreach($dokter as $d)
                                <option value="{{ $d->id }}"
                                    {{ $reservasi->dokter_id == $d->id ? 'selected' : '' }}>
                                    {{ $d->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="small text-secondary">Tanggal Kunjungan</label>
                        <input type="date" name="tanggal_pesan"
                               value="{{ $reservasi->tanggal_pesan }}"
                               class="form-control bg-dark text-white border-secondary">
                    </div>

                    <div class="mb-3">
                        <label class="small text-secondary">Jam Mulai</label>
                        <input type="time" name="jam_mulai"
                               value="{{ $reservasi->jam_mulai }}"
                               class="form-control bg-dark text-white border-secondary">
                    </div>

                    <div class="mb-3">
                        <label class="small text-secondary">Jam Selesai</label>
                        <input type="time" name="jam_selesai"
                               value="{{ $reservasi->jam_selesai }}"
                               class="form-control bg-dark text-white border-secondary">
                    </div>

                    <div class="mb-3">
                        <label class="small text-secondary">Keluhan</label>
                        <textarea name="keluhan" class="form-control bg-dark text-white border-secondary" rows="3">{{ $reservasi->keluhan }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="small text-secondary">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-select bg-dark text-white border-secondary">
                            <option value="Cash"     {{ $reservasi->metode_pembayaran == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Transfer" {{ $reservasi->metode_pembayaran == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer border-secondary">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-warning">Simpan Perubahan</button>
                </div>

            </form>

        </div>
    </div>
</div>



<!-- ============================= -->
<!-- MODAL: KONFIRMASI VERIFIKASI -->
<!-- ============================= -->
<div class="modal fade" id="modalKonfirmasiVerifikasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #1A1A1A; border:1px solid #444">

            <form action="{{ route('reservasi.admin.verifyPayment', $reservasi->id) }}" method="POST">
                @csrf

                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white">Verifikasi Pembayaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-white">
                    <p class="mb-0">
                        Apakah Anda yakin ingin 
                        <span class="text-success fw-bold">MEMVERIFIKASI</span> pembayaran ini?
                    </p>
                </div>

                <div class="modal-footer border-secondary">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-success">Ya, Verifikasi</button>
                </div>

            </form>

        </div>
    </div>
</div>

@endsection
