@extends('layouts.adminlte')

@section('title', 'Daftar Promo')

@section('styles')
<style>
    /* Card Background */
    .card-custom {
        background-color: #1A1A1A;
        border: 1px solid #333333;
        border-radius: 12px;
    }

    /* Tombol Gradasi Emas */
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
        transition: opacity 0.3s;
    }
    .btn-gold:hover { opacity: 0.9; color: #000; }

    /* Table Styles */
    .table-custom {
        --bs-table-bg: #1A1A1A;
        --bs-table-color: #E0E0E0;
        --bs-table-border-color: #333333;
    }
    .table-custom th {
        background-color: #2C2C2C;
        color: #9ca3af;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 16px;
        border-bottom: none;
    }
    .table-custom td {
        padding: 16px;
        vertical-align: middle;
        font-size: 0.875rem;
    }
    
    /* Banner Thumb */
    .banner-thumb {
        width: 80px;
        height: 45px;
        object-fit: cover;
        border-radius: 6px;
        background-color: #333;
        border: 1px solid #444;
    }

    /* Action Buttons Style */
    .btn-action {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .btn-action span { font-size: 18px; }
    .btn-action-edit {
        background-color: rgba(245, 197, 66, 0.1);
        color: #f5c542;
        border: 1px solid rgba(245, 197, 66, 0.3);
    }
    .btn-action-edit:hover {
        background-color: #f5c542;
        color: #000;
    }
    .btn-action-delete {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .btn-action-delete:hover {
        background-color: #ef4444;
        color: #fff;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    {{-- Header Section --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 2.5rem; letter-spacing: -1px;">Daftar Promo</h1>
            <p class="text-secondary mb-0">Kelola promo dan gamifikasi yang aktif untuk pasien 3K Dental Care.</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <a href="{{ route('promo.create') }}" class="btn btn-gold">
                <span class="material-symbols-outlined" style="font-size: 20px;">add</span>
                Tambah Promo Baru
            </a>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" 
             style="background-color: rgba(40, 167, 69, 0.1); border: 1px solid rgba(40, 167, 69, 0.4); color: #4ade80;">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Table Section --}}
    <div class="card card-custom overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th scope="col">Banner</th>
                        <th scope="col">Info Promo</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Potongan</th>
                        <th scope="col">Poin</th>
                        <th scope="col">Limit</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promos as $promo)
                    <tr>
                        <td>
                            @if($promo->gambar_banner)
                                <img src="{{ asset('storage/' . $promo->gambar_banner) }}" alt="Banner" class="banner-thumb">
                            @else
                                <div class="banner-thumb d-flex align-items-center justify-content-center text-secondary text-xs">No Img</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-white mb-1">{{ $promo->judul_promo }}</div>
                            <div class="text-secondary small" style="max-width: 250px;">{{ Str::limit($promo->deskripsi, 50) }}</div>
                        </td>
                        <td>
                            <div class="text-white small">{{ \Carbon\Carbon::parse($promo->tanggal_mulai)->format('d M Y') }}</div>
                            <div class="text-secondary small" style="font-size: 0.75rem;">s/d {{ \Carbon\Carbon::parse($promo->tanggal_selesai)->format('d M Y') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25">
                                Rp {{ number_format($promo->nilai_potongan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            <span class="text-warning fw-bold">{{ $promo->harga_poin }}</span> <span class="text-secondary small">pts</span>
                        </td>
                        <td>
                            <span class="text-white">{{ $promo->limit_per_user }}x</span> <span class="text-secondary small">/user</span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                {{-- Tombol Edit --}}
                                <a href="{{ route('promo.edit', $promo->id) }}" class="btn-action btn-action-edit" title="Edit">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                {{-- Tombol Hapus --}}
                                <form action="{{ route('promo.destroy', $promo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus promo ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Hapus">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.5;">folder_off</span>
                            <p class="mb-0 mt-2">Belum ada data promo.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-4">{{-- {{ $promos->links() }} --}}</div>
</div>
@endsection