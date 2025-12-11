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
    <div class="mb-4">
        <h1 class="fw-bold mb-1 text-gold" style="font-size: 1.8rem;">Tambah Reservasi Baru</h1>
        <p class="text-muted mb-0">Isi detail di bawah ini untuk membuat jadwal reservasi baru</p>
    </div>
    {{-- END HEADER --}}

    <form action="{{ route('reservasi.admin.store') }}" method="POST" id="formReservasiBaru">
        @csrf 
         
        {{-- Input tersembunyi untuk ID Pasien Lama --}}
        <input type="hidden" id="pasien_id_exist" name="pasien_id_exist" value="">

        {{-- === BAGIAN 1: DATA PASIEN === --}}
        <div class="form-group-section">
            <h4 class="section-title"><i class="fas fa-address-card"></i> Data Pasien</h4>
             
            {{-- FITUR PENCARIAN PASIEN LAMA --}}
            <div class="form-group">
                <label for="search_pasien" class="text-white">Cari Pasien Lama (No RM / Nama)</label>
                <div class="search-pasien-group">
                    <input type="text" class="form-control" id="search_pasien" placeholder="Masukkan Nomor Rekam Medis (RM) atau Nama...">
                    <button type="button" id="btn_search_pasien">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <small id="pasien_status" class="text-muted mt-2 d-block">&nbsp;</small> 
            </div>
             
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
                            <option value="baru">Baru</option>
                            <option value="lama">Lama</option>
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
                    {{-- 🔥 FIX 1: Ganti name="poli_id" jadi name="poli" --}}
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
                    {{-- 🔥 FIX 2: Ganti name="dokter_id" jadi name="dokter" --}}
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
                        <option value="" class="text-muted">Pilih Metode</option>
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="debit">Kartu Debit</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="status_bayar" class="text-white">Status Pembayaran</label>
                    <select class="form-control custom-select" id="status_bayar" name="status_bayar" required>
                        <option value="belum">Belum Bayar</option>
                        <option value="dp">DP/Uang Muka</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="total_biaya" class="text-white">Total Biaya</label>
                    <input type="text" class="form-control" id="total_biaya" name="total_biaya" placeholder="Masukkan Jumlah Biaya (Contoh: 500000)">
                </div>
            </div>
        </div> 

        <!-- FOOTER -->
        <div class="action-footer d-flex justify-content-end gap-3">
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
            locale: "id", 
            wrap: true 
        });
         
        // 2. Logic Pencarian Pasien Lama (AJAX)
        const btnSearch = document.getElementById('btn_search_pasien');
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

        function resetPasienFields(isReadOnly = false) {
            formPasienFields.forEach(field => {
                field.value = '';
                field.readOnly = isReadOnly;
                field.required = !isReadOnly;
            });
            pasienIdExistInput.value = '';
            pasienStatus.innerHTML = isReadOnly 
                ? 'Data Pasien Lama ditemukan. Form dikunci.'
                : '&nbsp;'; 
            pasienStatus.classList.remove('text-warning', 'text-success');
            pasienStatus.classList.add('text-muted');
        }

        btnSearch.addEventListener('click', function() {
            const query = searchInput.value.trim();
            // FIX: Minimal 3 karakter untuk pencarian
            if (query.length < 3) {
                pasienStatus.textContent = 'Masukkan minimal 3 karakter untuk mencari.';
                pasienStatus.classList.add('text-warning');
                resetPasienFields(false);
                return;
            }

            pasienStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari...';

            // 🔥 FIX 3: AJAX parameter jadi '?q=' sesuai Controller
            fetch("{{ route('reservasi.admin.cariPasien') }}?q=" + query)
                .then(response => response.json())
                .then(data => {
                    // Controller return array [ {...}, {...} ]
                    if (Array.isArray(data) && data.length > 0) {
                        const pasien = data[0]; // Ambil data pertama
                         
                        // Isi data pasien
                        document.getElementById('nama_lengkap').value = pasien.nama || '';
                        document.getElementById('ttl').value = pasien.tgl_lahir || ''; 
                        document.getElementById('alamat').value = pasien.alamat || '';
                        document.getElementById('no_hp').value = pasien.no_hp || '';
                        document.getElementById('jenis_pasien').value = pasien.jenis_pasien || 'lama';

                        // Set ID Pasien Lama
                        pasienIdExistInput.value = pasien.id; 
                         
                        // Kunci field
                        resetPasienFields(true);
                        pasienStatus.innerHTML = `Pasien Ditemukan: <span class="text-gold">${pasien.rekam_medis} - ${pasien.nama}</span>. Form dikunci.`;

                    } else {
                        // Pasien tidak ditemukan
                        resetPasienFields(false);
                        pasienStatus.textContent = 'Pasien Lama tidak ditemukan. Silakan isi data pasien baru.';
                        pasienStatus.classList.remove('text-muted');
                        pasienStatus.classList.add('text-warning');
                    }
                })
                .catch(error => {
                    console.error('Error AJAX:', error);
                    pasienStatus.textContent = 'Terjadi kesalahan saat mencari data.';
                    pasienStatus.classList.add('text-danger');
                    resetPasienFields(false);
                });
        });
         
        searchInput.addEventListener('input', function() {
            if (this.value.trim().length === 0 && pasienIdExistInput.value !== '') {
                resetPasienFields(false);
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

            fetch("{{ route('reservasi.admin.store') }}", { 
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
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
                        window.location.href = "{{ route('reservasi.admin.index') }}"; 
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan validasi',
                        background: '#1e1e2d',
                        color: '#fff',
                        confirmButtonColor: '#D4AF37'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan sistem.',
                    background: '#1e1e2d',
                    color: '#fff'
                });
                console.error('Error:', error);
            })
            .finally(() => {
                btnSave.innerHTML = originalText;
                btnSave.disabled = false;
            });
        });
    });
</script>
@stop