@extends('layouts.adminlte')

@section('title', 'Detail Reservasi Home Care')

@section('styles')
<style>
    /* Styling Card & Dark Mode Support */
    .card-dark {
        background-color: #1A1A1A;
        border: 1px solid #333333;
        border-radius: 0.75rem;
    }
    .text-gold { color: #f5c542; }
    
    /* Input di Dark Mode */
    .bg-dark-input {
        background-color: #2C2C2C !important;
        border: 1px solid #4b5563 !important;
        color: #fff !important;
    }
    .bg-dark-input:focus {
        border-color: #f5c542 !important;
        box-shadow: 0 0 0 0.25rem rgba(245, 197, 66, 0.25);
    }

    /* Badge Status Custom */
    .badge-status { font-size: 0.9rem; padding: 0.5em 0.8em; }

    /* --- PERBAIKAN: STYLE TOMBOL GOLD YANG HILANG --- */
    .btn-gold {
        background-image: linear-gradient(to right, #f5c542, #e4a93c);
        color: #121212;
        font-weight: 700;
        border: none;
        transition: opacity 0.3s;
    }
    .btn-gold:hover {
        opacity: 0.9;
        color: #000;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-white mb-1">Detail Reservasi</h1>
            <p class="text-secondary">
                No. Reservasi: <span class="text-gold fw-bold">{{ $item->no_pemeriksaan }}</span> 
                | Tgl: {{ \Carbon\Carbon::parse($item->tanggal_pesan)->format('d M Y') }}
            </p>
        </div>
        <a href="{{ route('homecare.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success text-white border-0 mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        {{-- KOLOM KIRI: INFO PASIEN & DOKTER --}}
        <div class="col-lg-8">
            {{-- Card Pasien --}}
            <div class="card card-dark p-4 mb-4">
                <h5 class="text-gold fw-bold mb-4 border-bottom border-secondary pb-2">
                    <i class="fas fa-user-injured me-2"></i> Data Pasien & Lokasi
                </h5>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-secondary small">Nama Pasien</label>
                        <p class="text-white fw-bold fs-5 mb-1">{{ $item->nama_pasien }}</p>
                        <small class="text-secondary"><i class="fas fa-phone me-1"></i> {{ $item->no_hp_pasien ?? '-' }}</small>
                    </div>
                    <div class="col-md-6">
                        <label class="text-secondary small">Jadwal Kunjungan</label>
                        <p class="text-white fw-bold mb-1">
                            {{ \Carbon\Carbon::parse($item->tanggal_pesan)->translatedFormat('l, d F Y') }}
                        </p>
                        <span class="badge bg-secondary">
                            <i class="far fa-clock me-1"></i> 
                            {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                        </span>
                    </div>
                    
                    <div class="col-12 mt-3">
                        <label class="text-secondary small">Alamat Lengkap (Lokasi Kunjungan)</label>
                        <div class="p-3 rounded" style="background-color: #252525; border-left: 4px solid #f5c542;">
                            <p class="mb-2 text-white">{{ $item->alamat_lengkap ?? 'Alamat tidak tersedia' }}</p>
                            
                            @if($item->latitude && $item->longitude)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $item->latitude }},{{ $item->longitude }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-light">
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i> Buka Google Maps
                                </a>
                                <small class="text-secondary ms-2">
                                    (Jarak: {{ $item->jarak_km ?? 0 }} km)
                                </small>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <label class="text-secondary small">Keluhan Utama</label>
                        <div class="p-3 rounded border border-secondary">
                            <p class="text-white fst-italic mb-0">"{{ $item->keluhan ?? '-' }}"</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card Dokter --}}
            <div class="card card-dark p-4">
                <h5 class="text-gold fw-bold mb-4 border-bottom border-secondary pb-2">
                    <i class="fas fa-user-md me-2"></i> Dokter Bertugas
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-user-md fs-2 text-white"></i>
                    </div>
                    <div>
                        @if($item->nama_dokter)
                            <h5 class="text-white mb-0">{{ $item->nama_dokter }}</h5>
                            <small class="text-secondary">Dokter Gigi Umum</small>
                        @else
                            <h5 class="text-danger mb-0">Belum Ada Dokter</h5>
                            <small class="text-secondary">Silakan assign dokter di menu Jadwal/Edit</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: STATUS TRACKING & BILLING --}}
        <div class="col-lg-4">
            
            {{-- Card Update Status --}}
            <div class="card card-dark p-4 mb-4 border-warning" style="border: 1px solid #f5c542;">
                <h5 class="text-white fw-bold mb-3">Workflow Status</h5>
                <p class="text-secondary small mb-3">Update status ini agar aplikasi mobile pasien berubah.</p>

                <div class="mb-3">
                    <label class="text-secondary small">Status Saat Ini:</label><br>
                    <span class="badge bg-warning text-dark fw-bold badge-status">
                        {{ strtoupper(str_replace('_', ' ', $item->status_reservasi ?? 'BARU')) }}
                    </span>
                </div>

                <form action="{{ route('homecare.updateStatus', $item->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="text-light small fw-bold mb-2">Ubah Status Ke:</label>
                        <select name="status" id="statusSelect" class="form-select bg-dark-input" onchange="checkStatus(this)">
                            <option disabled selected>-- Pilih Tindakan --</option>
                            
                            {{-- Mapping status database ke flow aplikasi --}}
                            <option value="menunggu_konfirmasi" {{ $item->status_reservasi == 'menunggu_konfirmasi' ? 'selected' : '' }}>
                                1. Konfirmasi Pesanan (Baru)
                            </option>
                            <option value="dokter_menuju_lokasi" {{ $item->status_reservasi == 'dokter_menuju_lokasi' ? 'selected' : '' }}>
                                2. Dokter OTW
                            </option>
                            <option value="sedang_diperiksa" {{ $item->status_reservasi == 'sedang_diperiksa' ? 'selected' : '' }}>
                                3. Sedang Diperiksa
                            </option>
                            <option value="menunggu_pelunasan" {{ $item->status_reservasi == 'menunggu_pelunasan' ? 'selected' : '' }}>
                                4. Selesai (Input Tagihan)
                            </option>
                            <option value="lunas" {{ $item->status_reservasi == 'lunas' ? 'selected' : '' }}>
                                5. Lunas / Selesai
                            </option>
                            <option value="dibatalkan" {{ $item->status_reservasi == 'dibatalkan' ? 'selected' : '' }} class="text-danger fw-bold">
                                X. Batalkan Pesanan
                            </option>
                        </select>
                    </div>

                    {{-- INPUT BIAYA (Hidden by default, shown via JS) --}}
                    {{-- Wajib diisi jika status diubah ke 'menunggu_pelunasan' --}}
                    <div class="mb-4 d-none p-3 rounded" id="biayaContainer" style="background-color: #2C2C2C; border: 1px dashed #f5c542;">
                        <label class="text-gold fw-bold mb-2">
                            <i class="fas fa-money-bill-wave me-1"></i> Total Biaya Tindakan (Rp)
                        </label>
                        <input type="number" 
                               name="total_biaya_tindakan" 
                               class="form-control bg-dark text-white border-secondary" 
                               placeholder="Contoh: 150000"
                               min="0"
                               value="{{ $item->total_biaya_tindakan > 0 ? $item->total_biaya_tindakan : '' }}">
                        <small class="text-danger mt-1 d-block" style="font-size: 0.75rem;">
                            * Admin WAJIB mengisi nominal ini agar pasien dapat melakukan pelunasan di aplikasi.
                        </small>
                    </div>

                    {{-- TOMBOL SAVE --}}
                    <button type="submit" class="btn btn-gold w-100 fw-bold">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>

            {{-- Card Rincian Biaya --}}
            <div class="card card-dark p-4">
                <h5 class="text-white fw-bold mb-3">Rincian Tagihan</h5>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-secondary d-flex justify-content-between px-0">
                        <span>Biaya Booking (DP)</span>
                        <span class="text-white">Rp {{ number_format($item->biaya_reservasi ?? 0, 0, ',', '.') }}</span>
                    </li>
                    <li class="list-group-item bg-transparent text-secondary d-flex justify-content-between px-0">
                        <span>Biaya Transport</span>
                        <span class="text-white">Rp {{ number_format($item->biaya_transport ?? 0, 0, ',', '.') }}</span>
                    </li>
                    
                    {{-- Tampilkan Biaya Tindakan jika sudah diinput --}}
                    <li class="list-group-item bg-transparent text-gold d-flex justify-content-between px-0 border-top border-secondary mt-2 pt-2">
                        <span>+ Biaya Tindakan</span>
                        <span class="fw-bold">
                            @if($item->total_biaya_tindakan > 0)
                                Rp {{ number_format($item->total_biaya_tindakan, 0, ',', '.') }}
                            @else
                                <span class="text-secondary fst-italic small">(Belum diinput)</span>
                            @endif
                        </span>
                    </li>

                    @if($item->potongan_promo > 0)
                    <li class="list-group-item bg-transparent text-success d-flex justify-content-between px-0">
                        <span>- Potongan Promo</span>
                        <span>- Rp {{ number_format($item->potongan_promo, 0, ',', '.') }}</span>
                    </li>
                    @endif

                    <li class="list-group-item bg-transparent text-white fw-bold d-flex justify-content-between px-0 border-top border-secondary mt-3 pt-3" style="font-size: 1.1rem;">
                        <span>Grand Total</span>
                        <span>
                            @php
                                $grandTotal = ($item->biaya_reservasi ?? 0) + 
                                              ($item->biaya_transport ?? 0) + 
                                              ($item->total_biaya_tindakan ?? 0) - 
                                              ($item->potongan_promo ?? 0);
                            @endphp
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </span>
                    </li>
                </ul>
                
                <div class="mt-3">
                    <span class="badge {{ $item->status_booking == 'lunas' || $item->status_pelunasan == 'lunas' ? 'bg-success' : 'bg-danger' }} w-100 py-2">
                        Status Pembayaran: {{ strtoupper($item->status_booking == 'lunas' && $item->status_pelunasan == 'lunas' ? 'LUNAS' : 'BELUM LUNAS') }}
                    </span>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- SCRIPT JAVASCRIPT --}}
<script>
    function checkStatus(select) {
        var biayaInput = document.getElementById('biayaContainer');
        var selectedValue = select.value;

        // Logic: Input biaya muncul HANYA jika status dipilih 'menunggu_pelunasan' (Selesai Tindakan)
        if (selectedValue === 'menunggu_pelunasan') {
            biayaInput.classList.remove('d-none');
            // Auto focus ke input biar admin sadar
            setTimeout(() => {
                biayaInput.querySelector('input').focus();
            }, 200);
        } else {
            biayaInput.classList.add('d-none');
        }
    }

    // Jalankan saat halaman dimuat (untuk handle jika halaman direfresh & status sudah selected)
    document.addEventListener("DOMContentLoaded", function() {
        var selectElement = document.getElementById('statusSelect');
        if(selectElement) {
            checkStatus(selectElement);
        }
    });
</script>
@endsection