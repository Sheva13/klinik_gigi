@extends('layouts.adminlte')

@section('title', 'Detail Reservasi')

@section('content')

<div class="container-fluid">

    <div class="d-flex align-items-center gap-3 mb-4">
        {{-- Tombol Kembali --}}
        <a href="{{ route('reservasi.admin.antrian') }}" class="btn btn-dark border-secondary d-flex align-items-center p-2 rounded-circle">
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

    {{-- KONTEN DETAIL --}}
    <div class="row align-items-stretch">
        
        {{-- KOLOM KIRI (Informasi Medis + Tracking) --}}
        <div class="col-lg-8 d-flex flex-column gap-4">
            
            {{-- CARD INFO MEDIS --}}
            <div class="card card-dark-premium border-0 mb-0">
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
                                        <span class="info-label text-secondary small">Data Pasien</span>
                                        <div class="info-value fs-5 mb-1 text-white fw-bold">{{ $reservasi->rekamMedis->nama ?? '-' }}</div>
                                        <div class="d-flex gap-2 mt-2 flex-wrap">
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary fw-normal">
                                                RM: {{ $reservasi->rekamMedis->rekam_medis ?? '-' }}
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
                                        <span class="info-label text-secondary small">Dokter Pemeriksa</span>
                                        <div class="info-value fs-5 mb-1 text-white fw-bold">{{ $reservasi->dokter->nama ?? '-' }}</div>
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
                                    <span class="info-label text-secondary small">Tanggal</span>
                                    <div class="text-white fw-bold">
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
                                    <span class="info-label text-secondary small">Jam Layanan</span>
                                    {{-- FIXED: Single line time format --}}
                                    <div class="text-white fw-bold text-nowrap">
                                        {{ \Carbon\Carbon::parse($reservasi->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($reservasi->jam_selesai)->format('H:i') }} WIB
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
                                    <span class="info-label text-secondary small">Keluhan Utama</span>
                                    <div class="text-white fw-medium fst-italic">
                                        "{{ $reservasi->keluhan ?? '-' }}"
                                    </div>
                                </div>
                            </div>
                        </div>
                     </div>
                </div>
            </div>

            {{-- CARD TRACKING (Visualisasi Status) --}}
            <div class="card card-dark-premium border-0 mb-4 flex-grow-1">
                <div class="card-header border-bottom border-secondary py-3">
                    <div class="d-flex align-items-center">
                         <span class="material-symbols-outlined text-info me-2">timeline</span>
                         <h5 class="mb-0 text-white fw-bold">Tracking Status Pasien</h5>
                    </div>
                </div>

                <div class="card-body p-4 d-flex flex-column justify-content-center">
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
                        <div class="d-flex align-items-center justify-content-between position-relative px-3 py-3">
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
                        <div class="mb-2"></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN (Pembayaran) --}}
        <div class="col-lg-4 d-flex flex-column">
             {{-- CARD PEMBAYARAN --}}
             <div class="card card-dark-premium border-0 h-100 flex-grow-1 mb-4">
                <div class="card-header border-bottom border-secondary py-3">
                    <div class="d-flex align-items-center">
                        <span class="material-symbols-outlined text-success me-2">payments</span>
                        <h5 class="mb-0 text-white fw-bold">Rincian Pembayaran</h5>
                    </div>
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

                        @php
                            $pStatus = $reservasi->status_pembayaran;
                            $isLunas = in_array($pStatus, ['lunas', 'terverifikasi']);
                            $alertClass = $isLunas ? 'alert-success' : 'alert-warning';
                            $icon = $isLunas ? 'check_circle' : 'pending';
                            $title = $isLunas ? 'LUNAS' : 'BELUM LUNAS';
                            $subtitle = strtoupper(str_replace('_', ' ', $pStatus));
                        @endphp

                        <div class="alert {{ $alertClass }} border-0 d-flex align-items-center" role="alert">
                            <span class="material-symbols-outlined me-2">{{ $icon }}</span>
                            <div>
                                <div class="fw-bold">{{ $title }}</div>
                                <small>{{ $subtitle }}</small>
                            </div>
                        </div>

                    </div>
                    <div class="mt-4">
                        @if(!$isLunas)
                            <a href="{{ route('reservasi.admin.showPayment', $reservasi->id) }}" class="btn btn-outline-light w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                <span class="material-symbols-outlined fs-6">receipt_long</span> Cek Bukti Transfer
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 🔥🔥 MODAL UTUH (DIPERBAIKI) 🔥🔥 --}}
<div class="modal fade" id="modalKelolaReservasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content modal-content-pro">

            <div class="modal-header border-secondary py-3">
                <h5 class="modal-title fw-bold text-white">
                    <span class="material-symbols-outlined align-middle text-warning me-2">tune</span>
                    Kelola Reservasi (Edit Lengkap)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('reservasi.admin.update', $reservasi->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body p-4">
                    <div class="row g-4 align-items-stretch">
                        {{-- KOLOM KIRI (Data Medis) --}}
                        <div class="col-lg-7">
                            <div class="p-3 rounded border border-secondary bg-dark h-100"> {{-- Added h-100 --}}
                                <h6 class="text-warning mb-3 fw-bold text-uppercase">Data Medis & Jadwal</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small text-secondary">Dokter</label>
                                        <select name="dokter_id" class="form-select form-select-pro">
                                            @foreach($dokters as $d)
                                                <option value="{{ $d->kode_dokter }}" {{ $reservasi->dokter_id == $d->kode_dokter ? 'selected' : '' }}>{{ $d->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-secondary">Tanggal</label>
                                        <input type="date" name="tanggal_pesan" value="{{ \Carbon\Carbon::parse($reservasi->tanggal_pesan)->format('Y-m-d') }}" class="form-control form-control-pro">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-secondary">Jam Mulai</label>
                                        <input type="time" name="jam_mulai" value="{{ \Carbon\Carbon::parse($reservasi->jam_mulai)->format('H:i') }}" class="form-control form-control-pro">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-secondary">Jam Selesai</label>
                                        <input type="time" name="jam_selesai" value="{{ \Carbon\Carbon::parse($reservasi->jam_selesai)->format('H:i') }}" class="form-control form-control-pro">
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-secondary">Keluhan</label>
                                        <textarea name="keluhan" class="form-control form-control-pro" rows="3">{{ $reservasi->keluhan }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KOLOM KANAN (Status & Keuangan) - DISATUKAN AGAR SAMA --}}
                        <div class="col-lg-5">
                             {{-- FIXED: Dijadikan SATU KOTAK BESAR (h-100) agar sama dengan sebelah kiri --}}
                             <div class="p-3 rounded border border-secondary bg-dark h-100">
                                {{-- Section Status --}}
                                <h6 class="text-info mb-3 fw-bold text-uppercase">Status Kunjungan</h6>
                                <div class="mb-4">
                                    <select name="status_reservasi" class="form-select form-select-pro">
                                        <option value="menunggu" {{ $reservasi->status_reservasi == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="dalam_proses" {{ $reservasi->status_reservasi == 'dalam_proses' ? 'selected' : '' }}>Dalam Proses</option>
                                        <option value="selesai" {{ $reservasi->status_reservasi == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                        <option value="batal" {{ $reservasi->status_reservasi == 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                                    </select>
                                </div>

                                {{-- Garis Pemisah --}}
                                <hr class="border-secondary opacity-25 my-4">

                                {{-- Section Keuangan --}}
                                <h6 class="text-success mb-3 fw-bold text-uppercase">Keuangan</h6>
                                <div class="mb-3">
                                    <label class="small text-secondary">Metode Bayar</label>
                                    <select name="metode_pembayaran" class="form-select form-select-pro">
                                        <option value="Manual" {{ $reservasi->metode_pembayaran == 'Manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="Cash" {{ $reservasi->metode_pembayaran == 'Cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="Transfer" {{ $reservasi->metode_pembayaran == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                        <option value="Midtrans" {{ $reservasi->metode_pembayaran == 'Midtrans' ? 'selected' : '' }}>Midtrans</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="small text-secondary">Status Pembayaran</label>
                                    <select name="status_pembayaran" class="form-select form-select-pro">
                                        <option value="menunggu_pembayaran" {{ $reservasi->status_pembayaran == 'menunggu_pembayaran' ? 'selected' : '' }}>Belum Bayar</option>
                                        <option value="menunggu_verifikasi" {{ $reservasi->status_pembayaran == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                        <option value="lunas" {{ $reservasi->status_pembayaran == 'lunas' ? 'selected' : '' }}>Lunas (Online)</option>
                                        <option value="terverifikasi" {{ $reservasi->status_pembayaran == 'terverifikasi' ? 'selected' : '' }}>Lunas (Manual)</option>
                                        <option value="gagal" {{ $reservasi->status_pembayaran == 'gagal' ? 'selected' : '' }}>Gagal</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top border-secondary p-3 bg-dark">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    {{-- FIXED: Text Capitalize (tidak Capslock) --}}
                    <button type="submit" class="btn btn-warning fw-bold px-5 text-dark shadow">Simpan Semua Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection