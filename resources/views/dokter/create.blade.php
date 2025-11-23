@extends('layouts.adminlte')

@section('title', 'Tambah Dokter Baru')

@section('styles')
<style>
    /* Styling Konsisten dengan Index */
    .card-dark {
        background-color: #1A1A1A;
        border: 1px solid #333;
        border-radius: 12px;
        color: #fff;
    }
    
    .form-control, .form-select {
        background-color: #2C2C2C;
        border: 1px solid #444;
        color: #fff;
        border-radius: 8px;
        padding: 10px 15px;
    }

    .form-control:focus, .form-select:focus {
        background-color: #2C2C2C;
        border-color: #f5c542; /* Gold focus */
        color: #fff;
        box-shadow: 0 0 0 0.2rem rgba(245, 197, 66, 0.25);
    }
    
    /* Input type date icon color fix */
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }

    .form-label {
        color: #9ca3af;
        font-weight: 500;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    /* Upload Area Styling */
    .upload-area {
        width: 100%;
        aspect-ratio: 4/5; 
        border: 2px dashed #4b5563;
        border-radius: 12px;
        background-color: #222;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .upload-area:hover {
        border-color: #f5c542;
        background-color: #2a2a2a;
    }

    .upload-area img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        top: 0;
        left: 0;
        display: none;
    }

    .upload-placeholder {
        text-align: center;
        color: #6b7280;
        z-index: 1;
    }

    .btn-gold {
        background-image: linear-gradient(to right, #f5c542, #e4a93c);
        color: #121212;
        font-weight: 700;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
    }
    .btn-gold:hover {
        opacity: 0.9;
        color: #000;
    }
    
    .btn-cancel {
        background-color: #374151;
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
    }
    .btn-cancel:hover {
        background-color: #4b5563;
        color: white;
    }
    
    .section-title {
        color: #f5c542;
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 1rem;
        border-bottom: 1px solid #333;
        padding-bottom: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    {{-- Header --}}
    <div class="mb-4">
        <h1 class="fw-bold text-white mb-1" style="font-size: 2rem;">Tambah Dokter Baru</h1>
        <p class="text-secondary">Lengkapi form di bawah ini sesuai data validasi dokter.</p>
    </div>

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="background-color: rgba(220, 53, 69, 0.1); border-color: #dc3545;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li class="small text-danger">{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('dokter.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row g-4">
            
            {{-- KOLOM KIRI: Foto & Info Dasar --}}
            <div class="col-12 col-lg-4">
                <div class="card card-dark p-4 h-100">
                    <h5 class="section-title">Foto Profil</h5>
                    
                    <div class="upload-area mb-4" onclick="document.getElementById('file_foto').click()">
                        <img id="preview_foto" src="#" alt="Preview Foto">
                        <div class="upload-placeholder" id="placeholder_content">
                            <span class="material-symbols-outlined" style="font-size: 3rem; color: #f5c542;">upload_file</span>
                            <p class="small mb-0 mt-2">Klik untuk upload foto</p>
                            <small class="text-xs text-secondary">(Format: JPG/PNG, Max 2MB)</small>
                        </div>
                    </div>
                    <input type="file" name="file_foto" id="file_foto" class="d-none" accept="image/*" onchange="previewImage(this)">

                    <h5 class="section-title mt-2">Identitas Dasar</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Kode Dokter <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="kode_dokter" value="{{ old('kode_dokter') }}" placeholder="Ex: DR-001" maxlength="15" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Inisial <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="inisial" value="{{ old('inisial') }}" placeholder="Ex: AS" maxlength="2" required>
                        <small class="text-secondary" style="font-size: 0.75rem;">Maksimal 2 karakter</small>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Form Detail Lengkap --}}
            <div class="col-12 col-lg-8">
                <div class="card card-dark p-4">
                    
                    {{-- SECTION 1: Data Pribadi --}}
                    <h5 class="section-title">Data Pribadi & Kontak</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" value="{{ old('nama') }}" placeholder="Tanpa gelar" maxlength="50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gelar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="gelar" value="{{ old('gelar') }}" placeholder="Contoh: drg. Sp.Ort" maxlength="50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. HP / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="hp" value="{{ old('hp') }}" placeholder="08..." maxlength="15" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alamat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="alamat" value="{{ old('alamat') }}" maxlength="50" required>
                        </div>
                    </div>

                    {{-- SECTION 2: Data Profesi --}}
                    <h5 class="section-title">Data Profesi</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Spesialisasi <span class="text-danger">*</span></label>
                            <select class="form-control form-select" name="spesialisasi" required>
                                <option value="" selected disabled>-- Pilih Spesialis --</option>
                                @foreach($spesialis as $s)
                                    <option value="{{ $s->id }}" {{ old('spesialisasi') == $s->id ? 'selected' : '' }}>
                                        {{ $s->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Poli <span class="text-secondary">(Opsional)</span></label>
                            <select class="form-control form-select" name="kode_poli">
                                <option value="" selected>-- Pilih Poli (Jika Ada) --</option>
                                @if(isset($polis))
                                    @foreach($polis as $p)
                                        <option value="{{ $p->kode_poli }}" {{ old('kode_poli') == $p->kode_poli ? 'selected' : '' }}>
                                            {{ $p->nama_poli }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- SECTION 3: Legalitas (STR & SIP) --}}
                    <h5 class="section-title">Data Legalitas (STR & SIP)</h5>
                    <div class="row g-3">
                        {{-- STR --}}
                        <div class="col-12">
                            <label class="form-label">Nomor STR <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="dokter_str" value="{{ old('dokter_str') }}" maxlength="250" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mulai Berlaku STR</label>
                            <input type="date" class="form-control" name="dokter_str_mulai" value="{{ old('dokter_str_mulai', '1960-01-01') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Masa Berakhir STR</label>
                            <input type="date" class="form-control" name="dokter_str_expire" value="{{ old('dokter_str_expire', '1960-01-01') }}">
                        </div>

                        {{-- SIP --}}
                        <div class="col-12 mt-3 border-top border-secondary pt-3">
                            <label class="form-label">Nomor SIP</label>
                            <input type="text" class="form-control" name="dokter_sip" value="{{ old('dokter_sip') }}" maxlength="250" placeholder="Opsional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mulai Berlaku SIP</label>
                            <input type="date" class="form-control" name="dokter_sip_berlaku" value="{{ old('dokter_sip_berlaku') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Masa Berakhir SIP</label>
                            <input type="date" class="form-control" name="dokter_sip_expired" value="{{ old('dokter_sip_expired') }}">
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top border-secondary">
                        <a href="{{ route('dokter.index') }}" class="btn btn-cancel">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-gold d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 20px;">save</span>
                            Simpan Data
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        var preview = document.getElementById('preview_foto');
        var placeholder = document.getElementById('placeholder_content');
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection