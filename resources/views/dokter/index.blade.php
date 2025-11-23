@extends('layouts.adminlte')

@section('title', 'Manajemen Data Dokter')

@section('styles')
<style>
    /* Style Kartu Dokter */
    .card-dokter {
        background-color: #1A1A1A; /* Dark background */
        border: 1px solid #333333;
        border-radius: 12px;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .card-dokter:hover {
        border-color: #f5c542; /* Primary Gold border on hover */
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.5);
    }

    /* Foto Profil Bulat */
    .dokter-avatar {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #4b5563; /* Gray border */
        margin: 0 auto 1rem auto;
        display: block;
        background-color: #333;
    }
    .card-dokter:hover .dokter-avatar {
        border-color: #f5c542; /* Gold border on hover */
    }
    
    /* Placeholder Inisial jika tidak ada foto */
    .avatar-placeholder {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 2px solid #4b5563;
        margin: 0 auto 1rem auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #2C2C2C;
        color: #f5c542;
        font-size: 2.5rem;
        font-weight: bold;
    }
    .card-dokter:hover .avatar-placeholder {
        border-color: #f5c542;
    }

    /* Tombol Gold (Reusable) */
    .btn-gold {
        background-image: linear-gradient(to right, #f5c542, #e4a93c);
        color: #121212;
        font-weight: 700;
        border: none;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
    }
    .btn-gold:hover {
        opacity: 0.9;
        color: #000;
    }

    /* Text Styles */
    .text-gold { color: #f5c542 !important; }
    .text-gray-400 { color: #9ca3af !important; }
    
    /* Label kecil untuk data */
    .data-label {
        font-size: 0.7rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .data-value {
        font-size: 0.85rem;
        color: #d1d5db;
        margin-bottom: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Header Section --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
        <div>
            <h1 class="fw-bold text-white mb-1" style="font-size: 2.5rem;">Manajemen Data Dokter</h1>
            <p class="text-secondary mb-0">Kelola data dokter, spesialisasi, dan jadwal praktik.</p>
        </div>
        
        <a href="{{ route('dokter.create') }}" class="btn btn-gold">
            <span class="material-symbols-outlined" style="font-size: 20px;">add</span>
            Tambah Dokter Baru
        </a>
    </div>

    {{-- Alert Success --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"
             style="background-color: rgba(40, 167, 69, 0.2); border: 1px solid #28a745; color: #28a745;">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <h4 class="text-white fw-bold mb-4 border-bottom border-secondary pb-3">Daftar Dokter Terdaftar</h4>

    {{-- Grid Dokter --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
        
        @forelse($dokters as $dokter)
        <div class="col">
            <div class="card card-dokter p-4 text-center">
                
                {{-- Logika Foto Profil (Sesuai Migration 'file_foto') --}}
                @if($dokter->file_foto && Storage::disk('public')->exists($dokter->file_foto))
                    <img src="{{ asset('storage/' . $dokter->file_foto) }}" 
                         alt="Foto {{ $dokter->nama }}" 
                         class="dokter-avatar">
                @else
                    {{-- Tampilkan Inisial jika tidak ada foto --}}
                    <div class="avatar-placeholder">
                        {{ $dokter->inisial ?? substr($dokter->nama, 0, 1) }}
                    </div>
                @endif
                
                {{-- Info Utama --}}
                <div class="mb-3">
    <h5 class="text-white fw-bold mb-1">{{ $dokter->gelar }} {{ $dokter->nama }}</h5>
    
    {{-- BAGIAN INI YANG NYAMBUNG KE TABEL SEBELAH --}}
    <p class="text-gold small fw-bold mb-2 text-uppercase">
        {{-- Jika relasi ditemukan, ambil namanya. Jika tidak, tampilkan fallback --}}
        {{ $dokter->spesialis->nama ?? 'Spesialisasi Tidak Ditemukan' }}
    </p>
</div>

                {{-- Detail Data (Sesuai Migration) --}}
                <div class="text-start bg-[#222] p-3 rounded mb-4" style="background-color: rgba(255,255,255,0.05);">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="data-label">Nomor STR</div>
                            <div class="data-value text-truncate" title="{{ $dokter->dokter_str }}">
                                {{ $dokter->dokter_str }}
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="data-label">Nomor SIP</div>
                            <div class="data-value text-truncate" title="{{ $dokter->dokter_sip }}">
                                {{ $dokter->dokter_sip ?? '-' }}
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="data-label">No. HP / WhatsApp</div>
                            <div class="data-value">
                                <span class="material-symbols-outlined align-middle me-1" style="font-size: 14px;">call</span>
                                {{ $dokter->hp }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex gap-2 mt-auto">
                    {{-- Edit Button (Pastikan route edit sudah ada) --}}
                    {{-- Asumsi Anda akan membuat route edit nanti, sementara pakai # --}}
                    <a href="#" class="btn btn-outline-warning w-100 d-flex align-items-center justify-content-center gap-2" style="border-color: #f5c542; color: #f5c542;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                        Edit
                    </a>
                    
                    {{-- Delete Button --}}
                    <form action="{{ route('dokter.destroy', $dokter->id) }}" method="POST" class="w-100" onsubmit="return confirm('Yakin ingin menghapus data dokter ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2" style="border-color: #ef4444; color: #ef4444;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                            Hapus
                        </button>
                    </form>
                </div>

            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5 text-secondary">
                <span class="material-symbols-outlined d-block mb-2" style="font-size: 48px;">person_off</span>
                <p class="mb-0">Belum ada data dokter.</p>
                <small>Silakan tambahkan data dokter baru.</small>
            </div>
        </div>
        @endforelse

    </div>
</div>
@endsection