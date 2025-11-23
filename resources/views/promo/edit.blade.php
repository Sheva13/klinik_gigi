@extends('layouts.adminlte')

@section('title', 'Edit Promo')

@section('styles')
<style>
    /* --- Copy Style dari Create agar konsisten --- */
    .form-control-dark {
        background-color: #2C2C2C;
        border: 1px solid #4b5563;
        color: #ffffff;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }
    .form-control-dark:focus {
        background-color: #2C2C2C;
        color: #ffffff;
        border-color: #f5c542;
        box-shadow: 0 0 0 0.25rem rgba(245, 197, 66, 0.25);
    }
    .form-control-dark::placeholder { color: #6b7280; }
    
    /* Agar icon kalender warnanya putih/terang */
    .form-control-dark[type="date"] { color-scheme: dark; }

    /* Upload Area */
    .upload-area {
        border: 2px dashed #4b5563;
        border-radius: 0.5rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .upload-area:hover {
        border-color: #f5c542;
        background-color: rgba(245, 197, 66, 0.05);
    }

    /* Tombol Gold */
    .btn-gold {
        background-image: linear-gradient(to right, #f5c542, #e4a93c);
        color: #121212;
        font-weight: 700;
        border: none;
    }
    .btn-gold:hover { opacity: 0.9; color: #000; }

    .card-dark {
        background-color: #1A1A1A;
        border: 1px solid #333333;
        border-radius: 0.75rem;
    }
    
    /* Preview Image di Form Edit */
    .current-img-container {
        width: 200px;
        height: 120px;
        background-color: #333;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        border: 1px solid #444;
    }
    .current-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-3">
        <div>
            <h1 class="fw-bold text-white mb-1" style="font-size: 2rem;">Edit Promo</h1>
            <p class="text-secondary mb-0">Perbarui detail promo yang sudah ada.</p>
        </div>
        
        <div class="d-flex align-items-center gap-2 text-secondary">
            <span class="small">Admin</span>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}" alt="Profile" style="width:40px;height:40px;object-fit:cover;border-radius:50%;"/>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="card card-dark p-4 p-md-5">
        {{-- Perhatikan route update dan method PUT --}}
        <form action="{{ route('promo.update', $promo->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Judul --}}
            <div class="mb-4">
                <label for="judul_promo" class="form-label text-light small fw-bold mb-2">Judul Promo</label>
                <input type="text" 
                       class="form-control form-control-dark @error('judul_promo') is-invalid @enderror" 
                       id="judul_promo" 
                       name="judul_promo" 
                       value="{{ old('judul_promo', $promo->judul_promo) }}" required>
            </div>

            {{-- Deskripsi --}}
            <div class="mb-4">
                <label for="deskripsi" class="form-label text-light small fw-bold mb-2">Deskripsi</label>
                <textarea class="form-control form-control-dark @error('deskripsi') is-invalid @enderror" 
                          id="deskripsi" 
                          name="deskripsi" 
                          rows="4" required>{{ old('deskripsi', $promo->deskripsi) }}</textarea>
            </div>

            {{-- Gambar Banner (Layout: Kiri Gambar Lama, Kanan Upload Baru) --}}
            <div class="mb-4">
                <label class="form-label text-light small fw-bold mb-2">Gambar Banner</label>
                
                <div class="d-flex flex-column flex-md-row gap-4 align-items-stretch">
                    
                    {{-- Gambar Saat Ini --}}
                    @if($promo->gambar_banner)
                    <div>
                        <p class="text-secondary small mb-1">Saat Ini:</p>
                        <div class="current-img-container">
                            <img src="{{ asset('storage/' . $promo->gambar_banner) }}" alt="Current Banner">
                        </div>
                    </div>
                    @endif

                    {{-- Area Upload --}}
                    <div class="flex-grow-1">
                        <p class="text-secondary small mb-1">Ganti Gambar (Opsional):</p>
                        <div class="upload-area" onclick="document.getElementById('gambar_banner').click()">
                            <span class="material-symbols-outlined text-warning mb-2" style="font-size: 32px;">upload_file</span>
                            <div class="text-light small fw-semibold">Klik untuk ganti file</div>
                            <p class="text-secondary small mb-0" style="font-size: 0.75rem;">atau drag and drop (Max 10MB)</p>
                            
                            <input type="file" id="gambar_banner" name="gambar_banner" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>
                    </div>
                </div>

                {{-- Preview Gambar Baru (Hidden by default) --}}
                <div id="preview-new-container" class="mt-3 d-none">
                    <p class="text-warning small mb-1">Akan diganti menjadi:</p>
                    <img id="preview-new-img" src="#" alt="New Preview" style="height: 100px; border-radius: 6px; border: 1px solid #f5c542;">
                </div>

                @error('gambar_banner')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal (Grid 2 Kolom) --}}
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <label for="tanggal_mulai" class="form-label text-light small fw-bold mb-2">Tanggal Mulai</label>
                    <div class="position-relative">
                        <input type="date" 
                               class="form-control form-control-dark" 
                               id="tanggal_mulai" 
                               name="tanggal_mulai"
                               value="{{ old('tanggal_mulai', $promo->tanggal_mulai) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="tanggal_selesai" class="form-label text-light small fw-bold mb-2">Tanggal Selesai</label>
                    <div class="position-relative">
                        <input type="date" 
                               class="form-control form-control-dark" 
                               id="tanggal_selesai" 
                               name="tanggal_selesai"
                               value="{{ old('tanggal_selesai', $promo->tanggal_selesai) }}" required>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="d-flex justify-content-end gap-3 pt-3 border-top border-secondary">
                <a href="{{ route('promo.index') }}" class="btn btn-secondary px-4 py-2 fw-bold" style="background-color: #374151; border:none;">
                    Batal
                </a>
                <button type="submit" class="btn btn-gold px-4 py-2 d-flex align-items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function previewImage(input) {
        const container = document.getElementById('preview-new-container');
        const preview = document.getElementById('preview-new-img');
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