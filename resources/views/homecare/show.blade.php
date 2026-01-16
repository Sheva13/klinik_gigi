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
                @if(isset($item->no_antrian))
                    | <span class="badge bg-gold text-dark ms-1">Urutan #{{ $item->no_antrian }}</span>
                @endif
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
                        <label class="text-secondary small">Nama Pasien & No RM</label>
                        <p class="text-white fw-bold fs-5 mb-0">{{ $item->nama_pasien ?? $item->nama_user ?? 'Nama Tidak Diketahui' }}</p>
                        <span class="badge bg-secondary font-monospace mb-2">{{ $item->no_rm ?? $item->pasien_id }}</span>
                        
                        <div class="small text-secondary mb-1">
                            <i class="fas fa-phone me-1"></i> {{ $item->no_hp_pasien ?? '-' }}
                        </div>
                        
                        <a href="{{ route('homecare.index', ['search' => $item->pasien_id]) }}" class="btn btn-sm btn-outline-warning py-0 px-2" style="font-size: 0.8rem; border-color: #f5c542; color: #f5c542;">
                            <i class="fas fa-history me-1"></i> Lihat Riwayat
                        </a>
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

            <!-- Dokter Information -->
            <div class="card bg-dark border-secondary mb-3">
                <div class="card-header border-secondary">
                    <h5 class="card-title mb-0 text-white">Dokter</h5>
                </div>
                <div class="card-body">
                    @if($item->nama_dokter)
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                {{ substr($item->nama_dokter, 0, 1) }}
                            </div>
                            <div>
                                <h5 class="mb-0 text-white">{{ $item->nama_dokter }}</h5>
                                <div class="text-secondary small">Dokter Gigi Umum</div>
                            </div>
                        </div>
                    @elseif($item->dokter_id)
                         <div class="d-flex align-items-center">
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                ?
                            </div>
                            <div>
                                <h5 class="mb-0 text-white">Kode: {{ $item->dokter_id }}</h5>
                                <div class="text-warning small fw-bold">Nama Tidak Ditemukan</div>
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-user-md fs-4"></i>
                            </div>
                            <div>
                                <p class="text-danger mb-0 fw-bold">Belum Ada Dokter</p>
                                <div class="small text-secondary">Silakan assign dokter di menu Jadwal.</div>
                            </div>
                        </div>
                    @endif
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
                                1. Menunggu Konfirmasi Admin
                            </option>
                            <option value="dokter_menuju_lokasi" {{ $item->status_reservasi == 'dokter_menuju_lokasi' ? 'selected' : '' }}>
                                2. Dokter Sedang Menuju Lokasi
                            </option>
                            <option value="sedang_diperiksa" {{ $item->status_reservasi == 'sedang_diperiksa' ? 'selected' : '' }}>
                                3. Sedang Dalam Pemeriksaan
                            </option>
                            <option value="menunggu_pelunasan" {{ $item->status_reservasi == 'menunggu_pelunasan' ? 'selected' : '' }}>
                                4. Pemeriksaan Selesai (Menunggu Pembayaran)
                            </option>
                            <option value="lunas" {{ $item->status_reservasi == 'lunas' ? 'selected' : '' }}>
                                5. Layanan Selesai & Lunas
                            </option>
                            <option value="dibatalkan" {{ $item->status_reservasi == 'dibatalkan' ? 'selected' : '' }} class="text-danger fw-bold">
                                X. Batalkan Reservasi
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
                <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">Rincian Tagihan</h5>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent text-secondary d-flex justify-content-between align-items-center px-0 py-2">
                        <span>Biaya Booking (DP)</span>
                        <span class="text-white text-end">Rp {{ number_format($item->biaya_reservasi ?? 0, 0, ',', '.') }}</span>
                    </li>
                    <li class="list-group-item bg-transparent text-secondary d-flex justify-content-between align-items-center px-0 py-2">
                        <span>Biaya Transport</span>
                        <span class="text-white text-end">Rp {{ number_format($item->biaya_transport ?? 0, 0, ',', '.') }}</span>
                    </li>
                    
                    {{-- Tampilkan Biaya Tindakan jika sudah diinput --}}
                    <li class="list-group-item bg-transparent text-gold d-flex justify-content-between align-items-center px-0 border-top border-secondary mt-2 pt-2">
                        <span>+ Biaya Tindakan</span>
                        <span class="fw-bold text-end">
                            @if($item->total_biaya_tindakan > 0)
                                Rp {{ number_format($item->total_biaya_tindakan, 0, ',', '.') }}
                            @else
                                <span class="text-secondary fst-italic small">(Belum diinput)</span>
                            @endif
                        </span>
                    </li>

                    @if($item->potongan_promo > 0)
                    <li class="list-group-item bg-transparent text-success d-flex justify-content-between align-items-center px-0">
                        <span>- Potongan Promo</span>
                        <span class="text-end">- Rp {{ number_format($item->potongan_promo, 0, ',', '.') }}</span>
                    </li>
                    @endif

                    @php
                        $grandTotal = ($item->biaya_reservasi ?? 0) + 
                                      ($item->biaya_transport ?? 0) + 
                                      ($item->total_biaya_tindakan ?? 0) - 
                                      ($item->potongan_promo ?? 0);
                    @endphp

                    <li class="list-group-item bg-transparent text-white fw-bold d-flex justify-content-between align-items-center px-0 border-top border-secondary mt-3 pt-3">
                        <span class="fs-5">Grand Total</span>
                        <span class="fs-5 text-end text-gold">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </span>
                    </li>
                </ul>
                
                <div class="mt-4 text-center">
                    <small class="text-secondary d-block mb-1">Status Pembayaran</small>
                    @if($item->status_booking == 'lunas' || $item->status_pelunasan == 'lunas')
                        <span class="badge bg-success fs-6 px-4 py-2 rounded-pill">
                            <i class="fas fa-check-circle me-1"></i> LUNAS
                        </span>
                    @else
                        <span class="badge bg-danger fs-6 px-4 py-2 rounded-pill">
                            <i class="fas fa-times-circle me-1"></i> BELUM LUNAS
                        </span>
                    @endif
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