@extends('layouts.adminlte')

@section('title', 'Tambah Promo Baru')

@section('styles')
<style>
    /* --- STYLE KHUSUS HALAMAN INI (Konversi Tailwind ke CSS Biasa) --- */

    /* Input Fields Dark Mode */
    .form-control-dark {
        background-color: #2C2C2C;
        border: 1px solid #4b5563; /* Gray-700 */
        color: #ffffff;
        border-radius: 0.5rem; /* Rounded-lg */
        padding: 0.75rem 1rem;
    }
    .form-control-dark:focus {
        background-color: #2C2C2C;
        color: #ffffff;
        border-color: #f5c542; /* Primary Gold */
        box-shadow: 0 0 0 0.25rem rgba(245, 197, 66, 0.25);
    }
    .form-control-dark::placeholder {
        color: #6b7280; /* Gray-500 */
    }
    
    /* Fix Date Input Icon Color in Dark Mode */
    .form-control-dark[type="date"] {
        color-scheme: dark;
    }

    /* Upload Area Styles */
    .upload-area {
        border: 2px dashed #4b5563;
        border-radius: 0.5rem;
        padding: 2.5rem 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
    }
    .upload-area:hover {
        border-color: #f5c542;
        background-color: rgba(245, 197, 66, 0.05);
    }
    .upload-icon {
        font-size: 3rem;
        color: #f5c542;
    }

    /* Tombol Gold (Sama seperti Index) */
    .btn-gold {
        background-image: linear-gradient(to right, #f5c542, #e4a93c);
        color: #121212;
        font-weight: 700;
        border: none;
    }
    .btn-gold:hover {
        opacity: 0.9;
        color: #000;
    }

    /* Card Custom */
    .card-dark {
        background-color: #1A1A1A;
        border: 1px solid #333333;
        border-radius: 0.75rem; /* Rounded-xl */
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Header Section --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="fw-bold text-white mb-1" style="font-size: 2rem;">Tambah Promo Baru</h1>
            <p class="text-secondary mb-0">Isi formulir di bawah untuk menambahkan promo baru ke dalam sistem.</p>
        </div>
        
        <div class="d-flex align-items-center gap-2 text-secondary">
            <span class="small">Admin</span>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}" 
                 alt="Profile" 
                 style="width:40px;height:40px;object-fit:cover;border-radius:50%;"/>
        </div>
    </div>

    {{-- Form Section --}}
    <div class="card card-dark p-4 p-md-5">
        <form action="{{ route('promo.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Judul Promo --}}
            <div class="mb-4">
                <label for="judul_promo" class="form-label text-light small fw-bold mb-2">Judul Promo</label>
                <input type="text" 
                       class="form-control form-control-dark @error('judul_promo') is-invalid @enderror" 
                       id="judul_promo" 
                       name="judul_promo" 
                       placeholder="cth: Promo Kemerdekaan"
                       value="{{ old('judul_promo') }}" required>
                @error('judul_promo')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="mb-4">
                <label for="deskripsi" class="form-label text-light small fw-bold mb-2">Deskripsi</label>
                <textarea class="form-control form-control-dark @error('deskripsi') is-invalid @enderror" 
                          id="deskripsi" 
                          name="deskripsi" 
                          rows="4" 
                          placeholder="cth: Diskon 17% untuk semua perawatan gigi" required>{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Upload Banner --}}
            <div class="mb-4">
                <label class="form-label text-light small fw-bold mb-2">Upload Gambar Banner</label>
                
                <div class="upload-area" onclick="document.getElementById('gambar_banner').click()">
                    <span class="material-symbols-outlined upload-icon mb-3">upload_file</span>
                    <div class="text-light small fw-semibold">
                        Klik untuk upload file
                    </div>
                    <p class="text-secondary small mb-0">atau tarik dan lepas disini</p>
                    <p class="text-secondary small mt-1" style="font-size: 0.75rem;">PNG, JPG, GIF hingga 10MB</p>
                    
                    {{-- Hidden Input --}}
                    <input type="file" 
                           id="gambar_banner" 
                           name="gambar_banner" 
                           class="d-none" 
                           accept="image/*"
                           onchange="previewImage(this)">
                </div>
                
                {{-- Preview (Javascript akan handle ini) --}}
                <div id="preview-container" class="mt-3 d-none">
                    <p class="text-light small mb-1">Preview:</p>
                    <img id="preview-img" src="#" alt="Preview" style="max-height: 150px; border-radius: 8px; border: 1px solid #444;">
                </div>

                @error('gambar_banner')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal --}}
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <label for="tanggal_mulai" class="form-label text-light small fw-bold mb-2">Tanggal Mulai</label>
                    <input type="date" 
                           class="form-control form-control-dark @error('tanggal_mulai') is-invalid @enderror" 
                           id="tanggal_mulai" 
                           name="tanggal_mulai"
                           value="{{ old('tanggal_mulai') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="tanggal_selesai" class="form-label text-light small fw-bold mb-2">Tanggal Selesai</label>
                    <input type="date" 
                           class="form-control form-control-dark @error('tanggal_selesai') is-invalid @enderror" 
                           id="tanggal_selesai" 
                           name="tanggal_selesai"
                           value="{{ old('tanggal_selesai') }}" required>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex justify-content-end gap-3 pt-3 border-top border-secondary">
                <a href="{{ route('promo.index') }}" class="btn btn-secondary px-4 py-2 fw-bold" style="background-color: #374151; border:none;">
                    Batal
                </a>
                <button type="submit" class="btn btn-gold px-4 py-2 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
                    Simpan Promo
                </button>
            </div>

        </form>
    </div>

</div>

<script>
    // Script sederhana untuk preview gambar sebelum upload
    function previewImage(input) {
        const container = document.getElementById('preview-container');
        const preview = document.getElementById('preview-img');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                container.classList.remove('d-none');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection