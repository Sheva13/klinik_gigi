@extends('layouts.adminlte') 

@section('title', 'Tambah Reservasi Baru')

@section('styles')
{{-- CSS untuk Flatpickr (Date Picker Library) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* DEFINISI VARIABEL WARNA */
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --bg-dark: #121212;
        --card-bg: #1A1A1A;
        --border-color: #333333;
        --text-muted: #a0a0a0;
        --input-bg: #121212;
    }

    /* GENERAL STYLES */
    h1, h2, h3, h4, h5, h6 { color: #fff !important; font-weight: 700 !important; }
    .text-gold { color: var(--gold-primary) !important; }
    .text-muted { color: var(--text-muted) !important; }

    /* FORM CONTAINER */
    .main-container { padding: 0 15px; } 

    /* FIELDSET/CARD SECTION */
    .form-group-section {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 25px 30px; 
        margin-bottom: 35px;
        background-color: var(--card-bg);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
    }
    
    /* JUDUL SECTION */
    .section-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #fff !important; 
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color); 
        letter-spacing: 0.5px;
    }
    .section-title i {
        color: var(--gold-primary); 
        font-size: 1.5rem;
    }

    /* INPUT Fields Customization */
    .form-group {
        margin-bottom: 15px;
    }
    .form-control, .custom-select, .flatpickr-input {
        background-color: var(--input-bg) !important;
        border: 1px solid var(--border-color) !important;
        color: #fff !important;
        border-radius: 8px !important;
        padding: 0.75rem 1rem !important;
    }
    .form-control:focus, .custom-select:focus, .flatpickr-input:focus {
        border-color: var(--gold-primary) !important;
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25) !important;
        background-color: var(--input-bg) !important;
    }

    /* INPUT GROUP FOR DATE/TIME PICKER */
    .input-group {
        width: 100%;
    }
    .input-group > .form-control,
    .input-group > .flatpickr-input {
        border-radius: 8px 0 0 8px !important; 
        border-right: none !important;
        z-index: 10;
        position: relative;
    }
    .input-group-append {
        z-index: 10; 
    }

    .input-group-append .input-group-text-custom {
        background-color: var(--input-bg) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 0 8px 8px 0 !important;
        padding: 0.75rem 1rem !important;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    
    .input-group:focus-within .input-group-text-custom {
        border-color: var(--gold-primary) !important;
    }
    
    /* INPUT GROUP for Search Pasien */
    .search-pasien-group {
        display: flex;
        align-items: center;
        width: 100%;
    }
    .search-pasien-group input {
        border-right: none !important;
        border-radius: 8px 0 0 8px !important;
    }
    .search-pasien-group button {
        background-color: var(--gold-primary);
        color: #000;
        border: none;
        border-radius: 0 8px 8px 0 !important;
        padding: 0.75rem 1rem;
        height: 100%; 
        display: flex;
        align-items: center;
    }
    .search-pasien-group button:hover {
        background-color: var(--gold-hover);
    }
    
    /* ACTION FOOTER (Sticky) */
    .action-footer {
        position: sticky;
        bottom: 0;
        padding: 15px 30px; 
        background-color: rgba(18, 18, 18, 0.95);
        border-top: 1px solid var(--border-color);
        z-index: 100;
        margin-left: -15px; 
        margin-right: -15px;
    }

    /* Buttons */
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

    .btn-secondary-dark {
        background-color: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        border-radius: 8px;
        padding: 0.75rem 2rem;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-secondary-dark:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: #FFF;
        border-color: #555;
    }
</style>
@endsection

@section('content')

<div class="main-container">

    {{-- HEADER KUSTOM --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        {{-- 🔥 TOMBOL KEMBALI DI HEADER (LINK KE INDEX) --}}
        <a href="{{ route('reservasi.admin.index') }}" class="btn btn-dark border-secondary d-flex align-items-center p-2 rounded-circle me-2">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="fw-bold mb-1 text-gold" style="font-size: 1.8rem;">Tambah Reservasi Baru</h1>
            <p class="text-muted mb-0">Isi detail di bawah ini untuk membuat jadwal reservasi baru</p>
        </div>
    </div>
    {{-- END HEADER --}}

    {{-- FORM UTAMA --}}
    <form action="{{ route('reservasi.admin.createManual') }}" method="POST" id="formReservasiBaru">
        @csrf 
        
        {{-- Input tersembunyi untuk ID Pasien Lama (Ini kuncinya!) --}}
        <input type="hidden" id="pasien_id_exist" name="pasien_id_exist" value="">

        {{-- === BAGIAN 1: DATA PASIEN === --}}
        <div class="form-group-section">
            <h4 class="section-title"><i class="fas fa-address-card"></i> Data Pasien</h4>
            
            {{-- SEARCH BAR (AJAX) --}}
            <div class="form-group">
                <label for="search_pasien" class="text-white">Cari Pasien Lama (No RM / Nama)</label>
                <div class="search-pasien-group">
                    <input type="text" class="form-control" id="search_pasien" placeholder="Masukkan Nomor Rekam Medis (RM) atau Nama...">
                    
                    <button type="button" id="btn_search_pasien" title="Cari Pasien" class="px-4 fw-bold">
                        <i class="fas fa-search me-2"></i> Cari
                    </button>
                    
                    {{-- Tombol Reset --}}
                    <button type="button" id="btn_reset_pasien" class="btn btn-secondary ms-2" style="border-radius: 8px; background-color: #333; color: #fff; border: 1px solid #444;" title="Reset Form">
                         <i class="fas fa-undo"></i>
                    </button>
                </div>
                <small id="pasien_status" class="text-muted mt-2 d-block">Biarkan kosong jika pasien baru. Ketik RM/Nama lalu klik tombol Cari.</small> 
            </div>
            
            {{-- FIELD DATA PASIEN (Akan otomatis terisi atau bisa diketik manual) --}}
            <div id="data_pasien_form">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="nama_lengkap" class="text-white">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="ttl" class="text-white">Tempat, Tanggal Lahir</label>
                        <input type="text" class="form-control" id="ttl" name="ttl" placeholder="Contoh: Jakarta, 01/01/2000" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="alamat" class="text-white">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                </div>
                
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="no_hp" class="text-white">No. HP</label>
                        <input type="tel" class="form-control" id="no_hp" name="no_hp" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="jenis_pasien" class="text-white">Jenis Pasien</label>
                        <select class="form-control custom-select" id="jenis_pasien" name="jenis_pasien" required>
                            <option value="Umum">Umum</option>
                            <option value="BPJS">BPJS</option>
                            <option value="Asuransi">Asuransi</option>
                        </select>
                    </div>
                </div>
            </div>
        </div> {{-- END Section: Data Pasien --}}

        {{-- === BAGIAN 2: DETAIL JANJI TEMU === --}}
        <div class="form-group-section">
            <h4 class="section-title"><i class="fas fa-calendar-check"></i> Detail Janji Temu</h4>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="poli" class="text-white">Poli</label>
                    <select class="form-control custom-select" id="poli" name="poli" required>
                        <option value="" class="text-muted">Pilih Poli</option>
                        @if(isset($polis))
                            @foreach($polis as $poli)
                                <option value="{{ $poli->kode_poli }}">{{ $poli->nama_poli }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="dokter" class="text-white">Dokter</label>
                    <select class="form-control custom-select" id="dokter" name="dokter" required>
                        <option value="" class="text-muted">Pilih Dokter</option>
                        @if(isset($dokters))
                            @foreach($dokters as $dokter)
                                <option value="{{ $dokter->kode_dokter }}">{{ $dokter->nama }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="tanggal_janji" class="text-white">Tanggal Janji Temu</label>
                    <div class="input-group" id="flatpickr-tanggal">
                        <input type="text" class="form-control date-picker" id="tanggal_janji" name="tanggal_janji" placeholder="Pilih Tanggal..." required data-input>
                        <div class="input-group-append">
                             <span class="input-group-text-custom" data-toggle>
                                 <i class="fas fa-calendar-alt text-gold"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label for="waktu_janji" class="text-white">Waktu Janji Temu</label>
                    <input type="text" class="form-control" id="waktu_janji" name="waktu_janji" placeholder="Masukkan Waktu (HH:MM)" required>
                </div>
            </div>
            <div class="form-group">
                <label for="keluhan" class="text-white">Keluhan</label>
                <textarea class="form-control" id="keluhan" name="keluhan" rows="3"></textarea>
            </div>
        </div> {{-- END Section: Detail Janji Temu --}}

        {{-- === BAGIAN 3: INFORMASI PEMBAYARAN === --}}
        <div class="form-group-section">
            <h4 class="section-title"><i class="fas fa-receipt"></i> Informasi Pembayaran</h4>
            <div class="row">
                <div class="form-group col-md-4">
                    <label for="metode_bayar" class="text-white">Metode Pembayaran</label>
                    <select class="form-control custom-select" id="metode_bayar" name="metode_bayar" required>
                        <option value="Manual" selected>Manual (Cash/Kartu)</option>
                        <option value="Midtrans">Midtrans (Jika sudah dibayar via link)</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="status_bayar" class="text-white">Status Pembayaran</label>
                    <select class="form-control custom-select" id="status_bayar" name="status_bayar" required>
                        <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                        <option value="terverifikasi" selected>Lunas / Terverifikasi</option>
                        <option value="gagal">Gagal / Ditolak</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="total_biaya" class="text-white">Total Biaya</label>
                    <input type="number" class="form-control" id="total_biaya" name="total_biaya" placeholder="Masukkan Jumlah Biaya (Contoh: 500000)" value="25000">
                </div>
            </div>
        </div> 

        <div class="action-footer d-flex justify-content-end gap-3">
            {{-- 🔥 TOMBOL BATAL JUGA KEMBALI KE INDEX --}}
            <a href="{{ route('reservasi.admin.index') }}" class="btn btn-secondary-dark">Batal</a>
            <button type="submit" class="btn btn-gold">
                <i class="fas fa-check-circle"></i> Konfirmasi & Buat Reservasi
            </button>
        </div>

    </form>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Flatpickr
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",
            minDate: "today", 
            wrap: true 
        });
        
        // 2. Logic Pencarian Pasien Lama (AJAX)
        const btnSearch = document.getElementById('btn_search_pasien');
        const btnReset = document.getElementById('btn_reset_pasien');
        const searchInput = document.getElementById('search_pasien');
        const pasienIdExistInput = document.getElementById('pasien_id_exist');
        const pasienStatus = document.getElementById('pasien_status');
        const formPasienFields = [
            document.getElementById('nama_lengkap'),
            document.getElementById('ttl'),
            document.getElementById('alamat'),
            document.getElementById('no_hp'),
            document.getElementById('jenis_pasien')
        ];

        // Helper: Reset atau Kunci Form
        function togglePasienFields(isReadOnly, clearValues = false) {
            formPasienFields.forEach(field => {
                if (clearValues) field.value = '';
                field.readOnly = isReadOnly;
                // Field tetap required meskipun readonly
            });

            if(!isReadOnly) {
                 // Mode Input Manual (Pasien Baru/Tidak Ketemu)
                 pasienIdExistInput.value = ''; 
                 pasienStatus.innerHTML = 'Mode Input Pasien Baru aktif.';
                 pasienStatus.classList.remove('text-success', 'text-danger', 'text-warning');
                 pasienStatus.classList.add('text-muted');
            }
        }

        // Event: Tombol Cari
        btnSearch.addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query.length < 3) {
                pasienStatus.textContent = 'Masukkan minimal 3 karakter untuk mencari.';
                pasienStatus.classList.add('text-warning');
                return;
            }

            pasienStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari...';

            // Panggil AJAX
            fetch("{{ route('reservasi.admin.cariPasien') }}?q=" + query)
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        // KASUS 1: PASIEN DITEMUKAN
                        const pasien = data[0]; 
                        
                        document.getElementById('nama_lengkap').value = pasien.nama_lengkap || '';
                        document.getElementById('ttl').value = pasien.tanggal_lahir || ''; 
                        document.getElementById('alamat').value = pasien.alamat_rumah || '';
                        document.getElementById('no_hp').value = pasien.nomor_telepon || '';
                        document.getElementById('jenis_pasien').value = pasien.tipe_pasien || 'Umum';

                        // Set ID agar controller tahu ini pasien lama
                        pasienIdExistInput.value = pasien.id_database; 
                        
                        // Kunci Form agar tidak diedit sembarangan (karena data database)
                        togglePasienFields(true, false);
                        
                        pasienStatus.innerHTML = `Pasien Ditemukan: <strong class="text-gold">${pasien.nomor_rm} - ${pasien.nama_lengkap}</strong>. Form dikunci.`;
                        pasienStatus.classList.remove('text-muted', 'text-warning', 'text-danger');
                        pasienStatus.classList.add('text-success');

                    } else {
                        // KASUS 2: PASIEN TIDAK DITEMUKAN -> BUKA INPUT AGAR BISA MANUAL
                        togglePasienFields(false, true); 
                        pasienStatus.textContent = 'Pasien tidak ditemukan. Silakan input data baru secara manual.';
                        pasienStatus.classList.remove('text-muted', 'text-success');
                        pasienStatus.classList.add('text-warning');
                    }
                })
                .catch(error => {
                    console.error('Error AJAX:', error);
                    pasienStatus.textContent = 'Terjadi kesalahan jaringan saat mencari data.';
                    pasienStatus.classList.add('text-danger');
                    togglePasienFields(false, false);
                });
        });

        // Event: Tombol Reset
        btnReset.addEventListener('click', function() {
            searchInput.value = '';
            // Buka kunci form & hapus isinya (Mode Manual)
            togglePasienFields(false, true); 
        });
        
        // Event: Saat input search dikosongkan manual
        searchInput.addEventListener('input', function() {
            if (this.value.trim().length === 0 && pasienIdExistInput.value !== '') {
                togglePasienFields(false, false);
            }
        });

        // --- SUBMIT FORM ---
        document.getElementById('formReservasiBaru').addEventListener('submit', function(e) {
            e.preventDefault(); 
            
            let formData = new FormData(this);
            let btnSave = this.querySelector('button[type="submit"]');
            let originalText = btnSave.innerHTML;
            
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            btnSave.disabled = true;

            fetch("{{ route('reservasi.admin.createManual') }}", { 
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        let errorMessage = 'Gagal menyimpan data.';
                        if (errorData.errors) {
                            errorMessage = Object.values(errorData.errors)[0][0]; 
                        } else if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                        throw new Error(errorMessage);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        background: '#1e1e2d',
                        color: '#fff',
                        confirmButtonColor: '#D4AF37'
                    }).then(() => {
                        // 🔥 Redirect Kembali ke Index setelah Sukses
                        window.location.href = "{{ route('reservasi.admin.index') }}"; 
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan.',
                        background: '#1e1e2d',
                        color: '#fff',
                        confirmButtonColor: '#D4AF37'
                    });
                }
            })
            .catch(error => {
                 Swal.fire({
                     icon: 'error',
                     title: 'Gagal',
                     text: error.message || 'Terjadi kesalahan sistem.',
                     background: '#1e1e2d',
                     color: '#fff',
                     confirmButtonColor: '#D4AF37'
                 });
            })
            .finally(() => {
                btnSave.innerHTML = originalText;
                btnSave.disabled = false;
            });
        });
    });
</script>
@stop