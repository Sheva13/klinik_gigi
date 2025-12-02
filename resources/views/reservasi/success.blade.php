@extends('layouts.adminlte')

@section('title', 'Reservasi Berhasil')

@section('content')

{{--
    Catatan: Style ini menggunakan asumsi bahwa file ini di-load di dalam
    konteks layout AdminLTE dan ingin menampilkan desain gelap (dark mode)
    yang konsisten dengan edit.blade.php Anda.

    Pastikan Google Material Symbols dimuat di layouts.adminlte untuk ikon.
--}}
<style>
    /* Menggunakan kembali variabel dari edit.blade.php */
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --bg-dark: #121212;
        --card-bg: #1A1A1A;
        --border-color: #333333;
        --text-muted: #a0a0a0;
        --input-bg: #121212;
    }

    /* Override warna teks default AdminLTE/Bootstrap */
    .content-wrapper {
        background-color: var(--bg-dark) !important;
        color: #fff;
    }
    h1, h2, h3, h4, h5, h6 { color: #fff !important; }
    .text-gold { color: var(--gold-primary) !important; }
    .text-muted { color: var(--text-muted) !important; }

    /* Gaya Card untuk Pop-up Sukses */
    .success-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 3rem;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.7);
        max-width: 600px; /* Batasi lebar untuk tampilan pop-up */
        width: 100%;
        text-align: center;
        margin: 0 auto !important; /* Pastikan di tengah horizontal */
    }

    /* Icon Checklist Emas */
    .success-icon {
        font-family: 'Material Symbols Outlined';
        font-weight: 400;
        font-size: 4rem;
        color: var(--gold-primary);
        line-height: 1;
        margin-bottom: 1.5rem;
        display: block; /* Memastikan ikon ada di baris sendiri */
        margin-left: auto;
        margin-right: auto;
    }

    /* Info Detail Reservasi */
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label {
        color: var(--text-muted);
        font-weight: 400;
        text-align: left;
    }
    .detail-value {
        color: #fff;
        font-weight: 600;
        text-align: right;
    }

    /* Gaya Tombol (ambil dari edit.blade.php) */
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
        text-decoration: none; /* Tambahkan agar tautan tidak bergaris bawah */
    }

    .btn-gold:hover { background-color: var(--gold-hover); color: #000; }

    .btn-cancel {
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

    .btn-cancel:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: #FFF;
        border-color: #555;
    }
</style>

<div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="success-card">
        {{-- Icon Centang Emas --}}
        <i class="material-symbols-outlined success-icon">check_circle</i>

        {{-- Judul dan Subjudul --}}
        <h2 class="fw-bold mb-2 text-gold">Reservasi Berhasil Dibuat!</h2>
        <p class="text-muted mb-4">Janji temu untuk pasien telah dikonfirmasi dan ditambahkan.</p>

        <hr style="border-color: var(--border-color); margin: 2rem 0;">

        {{-- Detail Reservasi --}}
        <div class="text-start px-md-5 mx-md-3 mb-5">
            
            @php
                // Pastikan Carbon terimport atau diakses via namespace jika dibutuhkan
                $carbon = new \Carbon\Carbon();
                
                // Pastikan tanggal_pesan adalah instance Carbon
                // Gunakan default value jika tanggal_pesan null untuk menghindari error
                $tanggalPesan = $reservasi->tanggal_pesan ? (\Carbon\Carbon::parse($reservasi->tanggal_pesan)) : $carbon->now();

                $reservasiData = [
                    'No RM' => $reservasi->rekamMedis?->rekam_medis ?? 'N/A', 
                    'Nama Lengkap' => $reservasi->rekamMedis?->nama ?? 'Pasien Tidak Ditemukan', 
                    'Tanggal Janji Temu' => $tanggalPesan->translatedFormat('d F Y'), // Menggunakan translatedFormat jika Carbon dikonfigurasi untuk Bhs Indonesia
                    'Waktu Janji Temu' => $tanggalPesan->format('H:i'), 
                    'Dokter' => $reservasi->dokter?->nama ?? 'N/A', 
                    'Poli' => $reservasi->dokter?->masterPoli?->nama_poli ?? 'N/A',
                    'No. Pemeriksaan' => $reservasi->no_pemeriksaan ?? 'N/A',
                ];
            @endphp

            @foreach ($reservasiData as $label => $value)
                <div class="detail-row">
                    <span class="detail-label">{{ $label }}</span>
                    <span class="detail-value">{{ $value }}</span>
                </div>
            @endforeach
        </div>

        {{-- Area Tombol --}}
        <div class="d-flex justify-content-center gap-3 flex-column flex-md-row">
            {{-- Tombol "Kembali ke Dashboard" menggunakan gaya .btn-cancel --}}
            <a href="{{ route('admin.dashboard') }}" class="btn btn-cancel">
                Kembali ke Dashboard
            </a>

            {{-- Tombol "Lanjut Buat Antrian" menggunakan gaya .btn-gold --}}
            <a href="{{ route('reservasi.admin.createAntrian', ['id' => $reservasi->id]) }}" class="btn btn-gold">
                Lanjut Buat Antrian
            </a>
        </div>
    </div>
</div>

@endsection