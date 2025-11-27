@extends('layouts.adminlte')

@section('title', 'Daftar Promo')

@section('styles')
<style>
    /* Custom Styles Khusus Halaman Ini untuk meniru Mockup Tailwind */
    
    /* Card Background menyerupai bg-[#1A1A1A] */
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
    .btn-gold:hover {
        opacity: 0.9;
        color: #000;
    }

    /* Table Styles */
    .table-custom {
        --bs-table-bg: #1A1A1A;
        --bs-table-color: #E0E0E0;
        --bs-table-border-color: #333333;
    }
    .table-custom th {
        background-color: #2C2C2C;
        color: #9ca3af; /* Text Gray */
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
    
    /* Banner Image Styling */
    .banner-thumb {
        width: 100px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        background-color: #333;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    {{-- Header Section --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5 gap-3">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 2.5rem; letter-spacing: -1px;">Daftar Promo</h1>
            <p class="text-secondary mb-0">Kelola promo yang aktif untuk pasien 3K Dental Care.</p>
        </div>
        
        <div class="d-flex align-items-center gap-4">
            {{-- Tombol Tambah --}}
            <a href="{{ route('promo.create') }}" class="btn btn-gold">
    <span class="material-symbols-outlined" style="font-size: 20px;">add</span>
    Tambah Promo Baru
</a>
            
            {{-- Profil Admin Kecil --}}
            
        </div>
    </div>

    {{-- Table Section --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" 
             style="background-color: rgba(40, 167, 69, 0.2); border: 1px solid #28a745; color: #28a745;">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card card-custom overflow-hidden">
        <div class="table-responsive">
            <table class="table table-custom mb-0 align-middle">
                <thead>
                    <tr>
                        <th scope="col">Judul Promo</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col">Gambar Banner</th>
                        <th scope="col">Tanggal Mulai</th>
                        <th scope="col">Tanggal Selesai</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Contoh Looping Data dari Database --}}
                    @forelse($promos as $promo)
                    <tr>
                        <td class="fw-bold text-white">{{ $promo->judul_promo }}</td>
                        <td class="text-secondary" style="max-width: 250px;">
                            {{ Str::limit($promo->deskripsi, 60) }}
                        </td>
                        <td>
                            @if($promo->gambar_banner)
                                {{-- Asumsi gambar disimpan di storage/app/public --}}
                                <img src="{{ asset('storage/' . $promo->gambar_banner) }}" alt="Banner" class="banner-thumb">
                            @else
                                <div class="banner-thumb d-flex align-items-center justify-content-center text-secondary text-xs">
                                    No Image
                                </div>
                            @endif
                        </td>
                        <td class="text-secondary">
                            {{ \Carbon\Carbon::parse($promo->tanggal_mulai)->format('d M Y') }}
                        </td>
                        <td class="text-secondary">
                            {{ \Carbon\Carbon::parse($promo->tanggal_selesai)->format('d M Y') }}
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('promo.edit', $promo->id) }}" class="btn btn-icon btn-sm text-warning" title="Edit">
    <span class="material-symbols-outlined">edit</span>
</a>
                                <form action="{{ route('promo.destroy', $promo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus promo ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-icon btn-sm text-danger" title="Hapus">
                <span class="material-symbols-outlined">delete</span>
            </button>
        </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <span class="material-symbols-outlined d-block mb-2" style="font-size: 48px;">folder_off</span>
                            Belum ada data promo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Pagination (Optional) --}}
    <div class="mt-4">
        {{-- {{ $promos->links() }} --}}
    </div>

</div>
@endsection