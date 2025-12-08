@extends('layouts.adminlte')

@section('title', 'Manajemen Pembayaran')

@section('content')

{{-- PASTIKAN BLOCK STYLE INI ADA AGAR TEMA DARK MODE & WARNA GOLD KONSISTEN --}}
<style>
    /* DEFINISI VARIABEL WARNA (Dari edit.blade.php) */
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --bg-dark: #121212;
        --card-bg: #1A1A1A;
        --border-color: #333333;
        --text-muted: #a0a0a0;
        --input-bg: #121212;
        --warning-status: #ffc107; /* Untuk status 'Belum Lunas' */
    }

    /* PENGATURAN FONT DAN WARNA DASAR UNTUK KERAPIHAN */
    h1, h2, h3, h4, h5, h6 { color: #fff !important; font-weight: 700 !important; }
    body { color: #fff; } /* Memastikan body default color putih */
    .text-gold { color: var(--gold-primary) !important; }
    .text-muted { color: var(--text-muted) !important; }
    
    /* Kelas Kustom untuk Badge Warning */
    .bg-warning-custom { background-color: var(--warning-status) !important; color: #000; } 

    /* Kelas Edit Card Utama (Wadah Konten) */
    .edit-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
    }

    /* Tombol Gold/Emas (Selesai) */
    .btn-gold {
        background-color: var(--gold-primary);
        color: #000;
        font-weight: 700;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 2rem;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-gold:hover { background-color: var(--gold-hover); color: #000; }

    /* Tombol Secondary (Batal) */
    .btn-secondary-dark {
        background-color: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        border-radius: 8px;
        padding: 0.75rem 2rem;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-block;
    }

    .btn-secondary-dark:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: #FFF;
        border-color: #555;
    }

    /* Dropzone Styling */
    .dropzone-dark {
        background-color: var(--input-bg);
        border: 2px dashed var(--border-color) !important;
        border-radius: 8px;
        padding: 3rem;
        color: #fff;
        transition: all 0.2s;
        cursor: pointer;
        display: block; /* <<< FIX INI AGAR LEBARNYA 100% */
    }
    .dropzone-dark:hover {
        border-color: var(--gold-primary) !important;
    }
    
    /* Style untuk daftar deskripsi (dl) agar rapi */
    .dl-horizontal dt {
        text-align: left;
        padding-right: 15px;
    }
</style>

<div class="container-fluid px-0">

    {{-- HEADER WITH ADMIN INFO --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Manajemen <span class="text-gold">Pembayaran</span></h1>
            <p class="text-muted mb-0">Perbarui dan tinjau detail pembayaran untuk reservasi</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                {{-- Data Admin --}}
                <div class="fw-bold text-white">{{ Auth::guard('admin')->user()->nama ?? 'Basudewa' }}</div>
                <small class="text-muted">Administrator</small>
            </div>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}"
                 alt="Profile"
                 class="rounded-circle border border-secondary"
                 style="width: 45px; height: 45px; object-fit: cover;">
        </div>
    </div>
    {{-- END HEADER --}}


    {{-- MAIN CONTENT CARD --}}
    <div class="edit-card">
        {{-- FORM DENGAN ENCTYPE UNTUK UPLOAD FILE --}}
        <form action="{{ route('reservasi.admin.tandaiLunas', $reservasi->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- 1. RINGKASAN RESERVASI --}}
            <div class="mb-5">
                <h4 class="fw-bold mb-3 text-white">Ringkasan Reservasi</h4>
                
                <div class="row text-white g-3">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4 text-muted">Nama Pasien</dt>
                            <dd class="col-sm-8 fw-bold">{{ $reservasi->rekamMedis->nama ?? 'Farel Sheva' }}</dd>

                            <dt class="col-sm-4 text-muted">Layanan</dt>
                            <dd class="col-sm-8">{{ $reservasi->layanan ?? 'Periksa Gigi' }}</dd>

                            <dt class="col-sm-4 text-muted">Status Pembayaran</dt>
                            <dd class="col-sm-8">
                                @php
                                    $status = $reservasi->status_pembayaran ?? 'Belum Lunas';
                                    $class = ($status == 'Lunas') ? 'bg-success' : 'bg-warning-custom';
                                @endphp
                                <span class="badge {{ $class }} py-2 px-3 fw-bold">{{ $status }}</span>
                            </dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-4 text-muted">No RM</dt>
                            <dd class="col-sm-8 fw-bold text-gold">{{ $reservasi->rekamMedis->rekam_medis ?? 'RM002' }}</dd>

                            <dt class="col-sm-4 text-muted">Tanggal & Waktu</dt>
                            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($reservasi->tanggal_waktu ?? '2025-11-25 09:00:00')->format('d F Y') }}</dd>

                            <dt class="col-sm-4 text-muted">Jumlah Total</dt>
                            <dd class="col-sm-8">
                                <h4 class="text-white fw-bold">Rp {{ number_format($reservasi->jumlah_total ?? 25000, 0, ',', '.') }}</h4>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- 2. BUKTI PEMBAYARAN --}}
            <div class="mb-5 pt-3 border-top border-secondary">
                <h4 class="fw-bold mb-3 text-white">Bukti Pembayaran</h4>
                <div class="form-group">
                    <label for="bukti_pembayaran_input" class="dropzone-dark text-center">
                        <i class="fas fa-cloud-upload-alt fa-3x mb-3 text-gold"></i>
                        <p class="text-white fw-bold">Tarik lepas file atau cari</p>
                        <small class="text-muted">Format yang didukung: JPEG PNG PDF</small>
                        
                        {{-- DISPLAY FILE YANG SUDAH ADA / PREVIEW --}}
                        <div class="mt-4 text-left px-4">
                            @if($reservasi->bukti_pembayaran_file_name ?? null)
                                {{-- Jika file sudah ada, tampilkan detailnya --}}
                                <i class="far fa-file-pdf text-danger me-2"></i>
                                <span class="text-white" id="file_name_display">{{ $reservasi->bukti_pembayaran_file_name }}</span>
                                <a href="#" class="float-right text-danger ms-3"><i class="fas fa-trash"></i></a>
                                <a href="{{ Storage::url($reservasi->bukti_pembayaran_path) }}" target="_blank" class="float-right text-gold"><i class="fas fa-download"></i></a>
                            @else
                                <span class="text-muted" id="file_name_display">Belum ada bukti pembayaran diunggah.</span>
                            @endif
                        </div>
                    </label>
                    {{-- Input file tersembunyi --}}
                    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran_input" class="d-none">
                </div>
            </div>

            {{-- FOOTER ACTION BUTTONS --}}
            <div class="d-flex justify-content-end gap-3 pt-4 border-top border-secondary">
                
                {{-- Tombol BATAL (Kembali ke Page 3 Edit) --}}
                <a href="{{ route('reservasi.admin.edit', $reservasi->id) }}" class="btn btn-secondary-dark">
                    Batal
                </a>
                
                {{-- Tombol SELESAI (Submit Form) --}}
                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-check-circle"></i> Selesai
                </button>
            </div>

        </form>
    </div>

</div>
{{-- Script untuk menampilkan nama file yang dipilih --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('bukti_pembayaran_input');
        
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const fileNameDisplay = document.getElementById('file_name_display');

                if (this.files.length > 0) {
                    fileNameDisplay.textContent = this.files[0].name;
                    fileNameDisplay.classList.remove('text-muted');
                    fileNameDisplay.classList.add('text-white');
                } else {
                    // Jika user membatalkan pilihan file
                    fileNameDisplay.textContent = 'Belum ada bukti pembayaran diunggah.';
                    fileNameDisplay.classList.add('text-muted');
                    fileNameDisplay.classList.remove('text-white');
                }
            });
        }
    });
</script>

@endsection