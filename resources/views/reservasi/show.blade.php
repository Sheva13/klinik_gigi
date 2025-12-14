@extends('layouts.adminlte')

@section('title', 'Detail Reservasi')

@section('content')

<style>
    /* --- CSS UTAMA HALAMAN --- */
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

    /* --- CSS KHUSUS MODAL (CLEAN & PROFESSIONAL) --- */
    .modal-content-pro {
        background-color: #181818; /* Lebih gelap dari card biasa */
        border: 1px solid #333;
        color: #e0e0e0;
        box-shadow: 0 0 50px rgba(0,0,0,0.9);
    }
    
    /* Container untuk setiap bagian form */
    .control-panel-box {
        background-color: #212121;
        border: 1px solid #333;
        border-radius: 8px;
        padding: 20px;
        height: 100%;
        position: relative;
    }
    
    /* Judul Section (misal: Edit Data) */
    .panel-title {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Style Input Form Gelap (Matte) */
    .form-control-pro, .form-select-pro {
        background-color: #121212 !important;
        border: 1px solid #444 !important;
        color: #fff !important;
        font-size: 0.9rem;
        padding: 10px 12px;
        border-radius: 6px;
        transition: border-color 0.2s;
    }
    .form-control-pro:focus, .form-select-pro:focus {
        background-color: #000 !important;
        border-color: #ffc107 !important; /* Aksen Kuning saat aktif */
        box-shadow: none !important;
        color: #fff !important;
    }
    
    /* Label kecil di atas input */
    .label-pro {
        font-size: 0.75rem;
        color: #888;
        margin-bottom: 6px;
        display: block;
        font-weight: 500;
    }

    /* Tombol Action */
    .btn-action-pro {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 20px;
        border-radius: 6px;
        width: 100%;
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

        <button class="btn btn-warning ms-auto px-4 fw-bold rounded-pill shadow-lg" data-bs-toggle="modal" data-bs-target="#modalKelolaReservasi">
            <span class="material-symbols-outlined align-middle me-2">settings_suggest</span> Kelola Reservasi
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
                        <h6 class="text-white fw-bold mb-0">
                            <span class="material-symbols-outlined align-middle text-info me-1">timeline</span>
                            Tracking Status Pasien
                        </h6>
                    </div>

                    @php
                        $status = $reservasi->status_reservasi;
                        $step = 0;
                        $inactiveLine = 'secondary bg-opacity-25';

                        if($status == 'menunggu') { $step = 1; }
                        elseif($status == 'dalam_proses') { $step = 2; }
                        elseif($status == 'selesai') { $step = 3; }
                        elseif($status == 'batal') { $step = 4; }
                        
                        $line1Color = ($step > 1) ? ($step == 3 ? 'success' : 'primary') : $inactiveLine;
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
                                <div class="tracking-dot shadow" style="background-color: {{ $step >= 1 ? '#ffc107' : '#252525' }}; border: 2px solid {{ $step >= 1 ? '#ffc107' : '#444' }};">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $step >= 1 ? '#000' : '#666' }}">hourglass_top</span>
                                </div>
                                <span class="position-absolute top-100 mt-2 small fw-bold text-nowrap {{ $step >= 1 ? 'text-warning' : 'text-secondary' }}">Menunggu</span>
                            </div>
                            <div class="flex-grow-1 mx-2 rounded-pill" style="height: 4px; background-color: var(--bs-{{ $line1Color }}); transition: all 0.5s;"></div>
                            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2;">
                                <div class="tracking-dot shadow" style="background-color: {{ $step >= 2 ? '#0d6efd' : '#252525' }}; border: 2px solid {{ $step >= 2 ? '#0d6efd' : '#444' }};">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $step >= 2 ? '#fff' : '#666' }}">medical_services</span>
                                </div>
                                <span class="position-absolute top-100 mt-2 small fw-bold text-nowrap {{ $step >= 2 ? 'text-primary' : 'text-secondary' }}">Diperiksa</span>
                            </div>
                            <div class="flex-grow-1 mx-2 rounded-pill" style="height: 4px; background-color: var(--bs-{{ $line2Color }}); transition: all 0.5s;"></div>
                            <div class="d-flex flex-column align-items-center position-relative" style="z-index: 2;">
                                <div class="tracking-dot shadow" style="background-color: {{ $step >= 3 ? '#198754' : '#252525' }}; border: 2px solid {{ $step >= 3 ? '#198754' : '#444' }};">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: {{ $step >= 3 ? '#fff' : '#666' }}">check_circle</span>
                                </div>
                                <span class="position-absolute top-100 mt-2 small fw-bold text-nowrap {{ $step >= 3 ? 'text-success' : 'text-secondary' }}">Selesai</span>
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
                            <h2 class="text-warning fw-bold mb-0 mt-1">Rp {{ number_format($reservasi->pembayaran_total, 0, ',', '.') }}</h2>
                        </div>
                         <div class="alert {{ $reservasi->status_pembayaran == 'terverifikasi' ? 'alert-success' : 'alert-warning' }} border-0 d-flex align-items-center" role="alert">
                            @if($reservasi->status_pembayaran == 'terverifikasi')
                                <span class="material-symbols-outlined me-2">check_circle</span>
                                <div><div class="fw-bold">LUNAS</div><small>Terverifikasi</small></div>
                            @else
                                <span class="material-symbols-outlined me-2">pending</span>
                                <div><div class="fw-bold">BELUM LUNAS</div><small>{{ strtoupper(str_replace('_', ' ', $reservasi->status_pembayaran)) }}</small></div>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4">
                        @if($reservasi->status_pembayaran != 'terverifikasi')
                            <a href="{{ route('admin.reservasi.pembayaran', $reservasi->id) }}" class="btn btn-outline-light w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                <span class="material-symbols-outlined fs-6">receipt_long</span> Cek Bukti Transfer
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKelolaReservasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-content-pro">
            
            <div class="modal-header border-secondary py-3">
                <h5 class="modal-title fw-bold">
                    <span class="material-symbols-outlined align-middle text-warning me-2">tune</span>
                    Kelola Reservasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-4">
                    
                    <div class="col-lg-8">
                        <form action="{{ route('reservasi.admin.update', $reservasi->id) }}" method="POST" class="h-100">
                            @csrf
                            @method('PUT')
                            
                            <div class="control-panel-box">
                                <div class="panel-title text-warning">
                                    <span class="material-symbols-outlined fs-5">edit_note</span>
                                    <span>Edit Data Medis</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="label-pro">Dokter Pemeriksa</label>
                                        <select name="dokter_id" class="form-select form-select-pro">
                                            @if(isset($dokters))
                                                @foreach($dokters as $d)
                                                    <option value="{{ $d->kode_dokter }}" {{ $reservasi->dokter_id == $d->kode_dokter ? 'selected' : '' }}>
                                                        {{ $d->nama }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="label-pro">Tanggal</label>
                                        <input type="date" name="tanggal_pesan" value="{{ $reservasi->tanggal_pesan }}" class="form-control form-control-pro">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="label-pro">Jam Mulai</label>
                                        <input type="time" name="jam_mulai" value="{{ $reservasi->jam_mulai }}" class="form-control form-control-pro">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="label-pro">Jam Selesai</label>
                                        <input type="time" name="jam_selesai" value="{{ $reservasi->jam_selesai }}" class="form-control form-control-pro">
                                    </div>
                                    <div class="col-12">
                                        <label class="label-pro">Keluhan Pasien</label>
                                        <textarea name="keluhan" class="form-control form-control-pro" rows="3">{{ $reservasi->keluhan }}</textarea>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-warning btn-action-pro shadow-sm text-dark">
                                            Simpan Perubahan Data
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-4">
                        <div class="d-flex flex-column gap-3 h-100">
                            
                            <form action="{{ route('reservasi.admin.status', $reservasi->id) }}" method="POST">
                                @csrf
                                <div class="control-panel-box">
                                    <div class="panel-title text-info">
                                        <span class="material-symbols-outlined fs-5">timeline</span>
                                        <span>Status Kunjungan</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="label-pro">Update Tracking Pasien</label>
                                        <select name="status_reservasi" class="form-select form-select-pro">
                                            <option value="menunggu" {{ $reservasi->status_reservasi == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                            <option value="dalam_proses" {{ $reservasi->status_reservasi == 'dalam_proses' ? 'selected' : '' }}>Dalam Proses</option>
                                            <option value="selesai" {{ $reservasi->status_reservasi == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                            <option value="batal" {{ $reservasi->status_reservasi == 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-outline-info btn-action-pro">
                                        Update Status
                                    </button>
                                </div>
                            </form>

                            <form action="{{ route('reservasi.admin.verifyPayment', $reservasi->id) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <div class="control-panel-box d-flex flex-column">
                                    <div class="panel-title text-success">
                                        <span class="material-symbols-outlined fs-5">payments</span>
                                        <span>Keuangan</span>
                                    </div>

                                    <div class="mb-3">
                                        <label class="label-pro">Metode Pembayaran</label>
                                        <select name="metode_pembayaran" class="form-select form-select-pro mb-3">
                                            <option value="Cash" {{ $reservasi->metode_pembayaran == 'Cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="Transfer" {{ $reservasi->metode_pembayaran == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                        </select>

                                        <label class="label-pro">Status Verifikasi</label>
                                        <select name="status_pembayaran" class="form-select form-select-pro">
                                            <option value="menunggu_pembayaran" {{ $reservasi->status_pembayaran == 'menunggu_pembayaran' ? 'selected' : '' }}>Belum Bayar</option>
                                            <option value="menunggu_verifikasi" {{ $reservasi->status_pembayaran == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Konfirmasi (Cek Bukti)</option>
                                            <option value="terverifikasi" {{ $reservasi->status_pembayaran == 'terverifikasi' ? 'selected' : '' }}>Lunas / Terverifikasi</option>
                                            <option value="gagal" {{ $reservasi->status_pembayaran == 'gagal' ? 'selected' : '' }}>Gagal / Ditolak</option>
                                        </select>
                                    </div>

                                    <div class="mt-auto">
                                        <button type="submit" class="btn btn-outline-success btn-action-pro">
                                            Simpan Status Bayar
                                        </button>
                                    </div>
                                </div>
                            </form>
                            
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection