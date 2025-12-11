@extends('layouts.adminlte')

@section('title', 'Detail Reservasi')

@section('content')
<style>
    /* === CSS TAMBAHAN UNTUK TAMPILAN PREMIUM === */
    .info-label {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 4px;
        display: block;
    }
    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: #fff;
    }
    .icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
    }
    .card-dark-premium {
        background-color: #1e1e1e;
        border: 1px solid #333;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
    }
    .tracking-dot {
        width: 32px; 
        height: 32px; 
        border-radius: 50%; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        z-index: 2;
        transition: all 0.3s ease;
    }
</style>

<div class="container-fluid">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('reservasi.admin.index') }}" class="btn btn-dark border-secondary d-flex align-items-center p-2 rounded-circle">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <span class="text-secondary small d-block">Detail Reservasi</span>
            <h1 class="h3 fw-bold mb-0 text-white">{{ $reservasi->no_pemeriksaan }}</h1>
        </div>

        <button class="btn btn-warning ms-auto px-4 fw-bold rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEditReservasi">
            <span class="material-symbols-outlined align-middle me-1">edit</span> Edit
        </button>
    </div>

    <div class="row">
        <div class="col-lg-8">
            
            <div class="card card-dark-premium mb-4 border-0">
                <div class="card-header border-bottom border-secondary py-3">
                    <div class="d-flex align-items-center">
                        <span class="material-symbols-outlined text-warning me-2">medical_information</span>
                        <h5 class="mb-0 text-white fw-bold">Informasi Medis & Jadwal</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 position-relative" style="background-color: #252525; border-left: 4px solid #ffc107;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                        <span class="material-symbols-outlined">person</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="info-label">Data Pasien</span>
                                        <div class="info-value fs-5 mb-1">{{ $reservasi->pasien->nama_lengkap ?? '-' }}</div>
                                        <div class="d-flex gap-2 mt-2 flex-wrap">
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary fw-normal">
                                                RM: {{ $reservasi->pasien->rekam_medis ?? '-' }}
                                            </span>
                                            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning fw-normal">
                                                {{ $reservasi->no_pemeriksaan }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded-3 position-relative" style="background-color: #252525; border-left: 4px solid #0dcaf0;">
                                <div class="d-flex align-items-start">
                                    <div class="icon-box bg-info bg-opacity-10 text-info">
                                        <span class="material-symbols-outlined">stethoscope</span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <span class="info-label">Dokter Pemeriksa</span>
                                        <div class="info-value fs-5 mb-1">{{ $reservasi->dokter->nama ?? '-' }}</div>
                                        <div class="text-info small">
                                            {{ $reservasi->jadwal->poli->nama_poli ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border-secondary opacity-25 my-4">

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-success bg-opacity-10 text-success">
                                    <span class="material-symbols-outlined">calendar_month</span>
                                </div>
                                <div>
                                    <span class="info-label">Tanggal</span>
                                    <div class="text-white fw-medium">
                                        {{ \Carbon\Carbon::parse($reservasi->tanggal_pesan)->locale('id')->translatedFormat('d F Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger">
                                    <span class="material-symbols-outlined">schedule</span>
                                </div>
                                <div>
                                    <span class="info-label">Jam Layanan</span>
                                    <div class="text-white fw-medium">
                                        {{ $reservasi->jam_mulai }} - {{ $reservasi->jam_selesai }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                    <span class="material-symbols-outlined">sick</span>
                                </div>
                                <div>
                                    <span class="info-label">Keluhan Utama</span>
                                    <div class="text-white fw-medium fst-italic">
                                        "{{ $reservasi->keluhan ?? '-' }}"
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card card-dark-premium border-0 mt-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <div>
                            <h6 class="text-white fw-bold mb-0">
                                <span class="material-symbols-outlined align-middle text-info me-1">timeline</span>
                                Tracking Status Pasien
                            </h6>
                        </div>
                        <button class="btn btn-sm btn-primary px-3 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUpdateStatusKunjungan">
                            <span class="material-symbols-outlined align-middle" style="font-size: 16px;">edit</span> Update
                        </button>
                    </div>

                    @php
                        $status = $reservasi->status_reservasi;
                        $step = 0;
                        $inactiveLine = 'secondary bg-opacity-25'; // Warna abu-abu transparan

                        if($status == 'menunggu') { $step = 1; }
                        elseif($status == 'dalam_proses') { $step = 2; }
                        elseif($status == 'selesai') { $step = 3; }
                        elseif($status == 'batal') { $step = 4; }
                        
                        // Garis 1 (Menunggu -> Diperiksa)
                        // Jika step 3 (selesai) jadi Hijau, jika step 2 (proses) jadi Biru
                        $line1Color = ($step > 1) ? ($step == 3 ? 'success' : 'primary') : $inactiveLine;
                        
                        // Garis 2 (Diperiksa -> Selesai)
                        // Hanya jadi Hijau jika step 3
                        $line2Color = ($step > 2) ? 'success' : $inactiveLine;
                    @endphp

                    @if($step == 4)
                        <div class="alert alert-danger bg-danger bg-opacity-10 border-danger d-flex align-items-center text-danger mb-0">
                            <span class="material-symbols-outlined me-3 fs-3">cancel</span>
                            <div>
                                <h6 class="fw-bold mb-0">Reservasi Dibatalkan</h6>
                                <small>Pasien ini telah membatalkan kunjungan.</small>
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-between position-relative px-3">
                            
                            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2;">
                                <div class="tracking-dot shadow" 
                                     style="background-color: {{ $step >= 1 ? '#ffc107' : '#252525' }}; 
                                            border: 2px solid {{ $step >= 1 ? '#ffc107' : '#444' }};">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $step >= 1 ? '#000' : '#666' }}">hourglass_top</span>
                                </div>
                                <span class="position-absolute top-100 mt-2 small fw-bold text-nowrap {{ $step >= 1 ? 'text-warning' : 'text-secondary' }}" style="transform: translateX(0%);">Menunggu</span>
                            </div>

                            <div class="flex-grow-1 mx-2 rounded-pill" style="height: 4px; background-color: var(--bs-{{ $line1Color }}); transition: all 0.5s;"></div>

                            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2;">
                                <div class="tracking-dot shadow" 
                                     style="background-color: {{ $step >= 2 ? '#0d6efd' : '#252525' }}; 
                                            border: 2px solid {{ $step >= 2 ? '#0d6efd' : '#444' }};">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $step >= 2 ? '#fff' : '#666' }}">medical_services</span>
                                </div>
                                <span class="position-absolute top-100 mt-2 small fw-bold text-nowrap {{ $step >= 2 ? 'text-primary' : 'text-secondary' }}" style="transform: translateX(0%);">Diperiksa</span>
                            </div>

                            <div class="flex-grow-1 mx-2 rounded-pill" style="height: 4px; background-color: var(--bs-{{ $line2Color }}); transition: all 0.5s;"></div>

                            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2;">
                                <div class="tracking-dot shadow" 
                                     style="background-color: {{ $step >= 3 ? '#198754' : '#252525' }}; 
                                            border: 2px solid {{ $step >= 3 ? '#198754' : '#444' }};">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $step >= 3 ? '#fff' : '#666' }}">check_circle</span>
                                </div>
                                <span class="position-absolute top-100 mt-2 small fw-bold text-nowrap {{ $step >= 3 ? 'text-success' : 'text-secondary' }}" style="transform: translateX(0%);">Selesai</span>
                            </div>

                        </div>
                        <div class="mb-4"></div> 
                    @endif
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="card card-dark-premium mb-4 border-0 h-100">
                <div class="card-header border-bottom border-secondary py-3">
                    <h5 class="mb-0 text-white fw-bold">Rincian Pembayaran</h5>
                </div>

                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <span class="text-secondary">Metode</span>
                            <span class="badge bg-dark border border-secondary text-white px-3 py-2">{{ $reservasi->metode_pembayaran }}</span>
                        </div>

                        <div class="text-center py-4 rounded-3 mb-4" style="background-color: #252525; border: 1px dashed #444;">
                            <small class="text-uppercase text-secondary letter-spacing-1">Total Biaya</small>
                            <h2 class="text-warning fw-bold mb-0 mt-1">
                                Rp {{ number_format($reservasi->pembayaran_total, 0, ',', '.') }}
                            </h2>
                        </div>

                         <div class="alert {{ $reservasi->status_pembayaran == 'terverifikasi' ? 'alert-success' : 'alert-warning' }} border-0 d-flex align-items-center" role="alert">
                            @if($reservasi->status_pembayaran == 'terverifikasi')
                                <span class="material-symbols-outlined me-2">check_circle</span>
                                <div>
                                    <div class="fw-bold">LUNAS</div>
                                    <small>Terverifikasi</small>
                                </div>
                            @else
                                <span class="material-symbols-outlined me-2">pending</span>
                                <div>
                                    <div class="fw-bold">BELUM LUNAS</div>
                                    <small>{{ strtoupper(str_replace('_', ' ', $reservasi->status_pembayaran)) }}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="btn btn-warning w-100 fw-bold py-2 mb-2" data-bs-toggle="modal" data-bs-target="#modalUpdatePembayaran">
                            Update Pembayaran
                        </button>
                        
                        @if($reservasi->status_pembayaran != 'terverifikasi')
                            <a href="{{ route('admin.reservasi.pembayaran', $reservasi->id) }}" class="btn btn-outline-light w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                <span class="material-symbols-outlined fs-6">receipt_long</span>
                                Cek Bukti Transfer
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                            @if(isset($dokters))
                                @foreach($dokters as $d)
                                    <option value="{{ $d->kode_dokter }}" {{ $reservasi->dokter_id == $d->kode_dokter ? 'selected' : '' }}>{{ $d->nama }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary">Tanggal Kunjungan</label>
                        <input type="date" name="tanggal_pesan" value="{{ $reservasi->tanggal_pesan }}" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small text-secondary">Jam Mulai</label>
                            <input type="time" name="jam_mulai" value="{{ $reservasi->jam_mulai }}" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-secondary">Jam Selesai</label>
                            <input type="time" name="jam_selesai" value="{{ $reservasi->jam_selesai }}" class="form-control bg-dark text-white border-secondary">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary">Keluhan</label>
                        <textarea name="keluhan" class="form-control bg-dark text-white border-secondary" rows="3">{{ $reservasi->keluhan }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary">Metode Pembayaran</label>
                        <select name="metode_pembayaran" class="form-select bg-dark text-white border-secondary">
                            <option value="Cash" {{ $reservasi->metode_pembayaran == 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Transfer" {{ $reservasi->metode_pembayaran == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUpdatePembayaran" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #1A1A1A; border:1px solid #444">
            <form action="{{ route('reservasi.admin.verifyPayment', $reservasi->id) }}" method="POST">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white">Konfirmasi Pembayaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-white">
                    <p class="mb-3">Silakan update status pembayaran ini:</p>
                    <div class="mb-3">
                        <label class="small text-secondary mb-1">Status Pembayaran</label>
                        <select name="status_pembayaran" class="form-select bg-dark text-white border-secondary">
                            <option value="menunggu_pembayaran" {{ $reservasi->status_pembayaran == 'menunggu_pembayaran' ? 'selected' : '' }}>Belum Bayar</option>
                            <option value="menunggu_verifikasi" {{ $reservasi->status_pembayaran == 'menunggu_verifikasi' ? 'selected' : '' }}>Cek Bukti (Menunggu)</option>
                            <option value="terverifikasi" {{ $reservasi->status_pembayaran == 'terverifikasi' ? 'selected' : '' }}>Lunas / Terverifikasi</option>
                            <option value="gagal" {{ $reservasi->status_pembayaran == 'gagal' ? 'selected' : '' }}>Gagal</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan Status Bayar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUpdateStatusKunjungan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #1A1A1A; border:1px solid #444">
            <form action="{{ route('reservasi.admin.status', $reservasi->id) }}" method="POST">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white">Update Proses Medis</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-white">
                    <p class="mb-3">Update status perjalanan pasien:</p>
                    <div class="mb-3">
                        <label class="small text-secondary mb-1">Status Kunjungan</label>
                        <select name="status_reservasi" class="form-select bg-dark text-white border-secondary">
                            <option value="menunggu" {{ $reservasi->status_reservasi == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="dalam_proses" {{ $reservasi->status_reservasi == 'dalam_proses' ? 'selected' : '' }}>Dalam Proses / Confirmed</option>
                            <option value="selesai" {{ $reservasi->status_reservasi == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="batal" {{ $reservasi->status_reservasi == 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection