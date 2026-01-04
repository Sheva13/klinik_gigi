@extends('layouts.adminlte')

@section('title', 'Tambah Reservasi Baru')

@section('styles')
{{-- 1. FontAwesome (Wajib ada agar ikon muncul) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

{{-- 2. CSS Flatpickr --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

{{-- 3. CSS Custom Kamu --}}
<link rel="stylesheet" href="{{ asset('dist/css/adminreservasi-custom.css') }}">

<style>
    /* 🔥 1. FIX SIDEBAR MENGECIL (PENTING) 🔥 */
    .sidebar {
        min-width: 260px !important;
        width: 260px !important;
        flex-shrink: 0 !important;
    }

    /* 🔥 2. STYLE INPUT RP GELAP 🔥 */
    .input-group-text-rp {
        background-color: #2F2F2F !important;
        color: #A0A0A0 !important;
        border: 1px solid var(--border-color, #333);
        border-right: none;
        font-weight: 600;
        padding-left: 15px;
        padding-right: 15px;
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    
    .input-biaya {
        background-color: #121212 !important;
        color: #fff !important; /* Angka Putih Tebal */
        border: 1px solid var(--border-color, #333);
        font-weight: 700;
        font-size: 1.1rem; 
        border-left: none;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    .input-biaya:focus {
        border-color: var(--gold-primary, #D4AF37) !important;
        box-shadow: none;
    }

    /* 🔥 3. STYLE INPUT KALENDER & JAM 🔥 */
    .input-group-text-custom {
        display: flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        background-color: var(--input-bg, #121212);
        border: 1px solid var(--border-color, #333);
        border-left: none;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
        color: #A0A0A0;
    }
    /* Menghilangkan border kanan pada input agar menyatu dengan ikon */
    .form-control.has-icon-right {
        border-right: none;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .form-control.has-icon-right:focus {
        border-color: var(--gold-primary, #D4AF37);
        box-shadow: none;
    }
    .form-control.has-icon-right:focus + .input-group-append .input-group-text-custom {
        border-color: var(--gold-primary, #D4AF37);
        border-left: none;
    }

    /* 4. Callout Info Penting */
    .callout-gold {
        border-left: 4px solid var(--gold-primary, #D4AF37);
        background-color: rgba(212, 175, 55, 0.05);
        border-radius: 4px;
        padding: 1rem;
    }
</style>
@endsection

@section('content')

<div class="main-container">

    {{-- HEADER --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('reservasi.admin.index') }}" class="btn btn-secondary-dark rounded-circle p-2" style="width: 45px; height: 45px; justify-content: center;">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            {{-- Judul Putih + Emas --}}
            <h1 class="fw-bold mb-1 text-white" style="font-size: 1.8rem;">
                Tambah Reservasi <span class="text-gold">Baru</span>
            </h1>
            <p class="text-muted mb-0">Isi detail di bawah ini untuk membuat jadwal reservasi baru</p>
        </div>
    </div>

    {{-- FORM UTAMA --}}
    <form action="{{ route('reservasi.admin.createManual') }}" method="POST" id="formReservasiBaru">
        @csrf
        <input type="hidden" id="pasien_id_exist" name="pasien_id_exist" value="">

        <div class="row">
            
            {{-- === KOLOM KIRI: DATA PASIEN === --}}
            <div class="col-lg-6 mb-4">
                <div class="form-group-section h-100">
                    <h4 class="mb-4 text-gold border-bottom border-secondary pb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-user-circle fs-4"></i> Data Pasien
                    </h4>

                    {{-- Search Bar --}}
                    <div class="form-group mb-3">
                        <label for="search_pasien" class="text-muted small mb-1">Cari Pasien Lama (No RM / Nama)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search_pasien" placeholder="Ketik RM atau Nama...">
                            <button type="button" id="btn_search_pasien" class="btn btn-gold">
                                <i class="fas fa-search text-dark"></i>
                            </button>
                            <button type="button" id="btn_reset_pasien" class="btn btn-secondary-dark ms-1">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                        <small id="pasien_status" class="text-muted mt-1 d-block" style="font-size: 0.8rem;">
                            Biarkan kosong jika pasien baru.
                        </small>
                    </div>

                    {{-- Form Data Pasien --}}
                    <div id="data_pasien_form">
                        <div class="form-group mb-3">
                            <label class="text-muted small mb-1">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 mb-3">
                                <label class="text-muted small mb-1">Tempat, Tanggal Lahir</label>
                                <input type="text" class="form-control" id="ttl" name="ttl" placeholder="Jakarta, 01/01/2000" required>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label class="text-muted small mb-1">No. HP</label>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="text-muted small mb-1">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label class="text-muted small mb-1">Jenis Pasien</label>
                            <select class="form-control" id="jenis_pasien" name="jenis_pasien" required>
                                <option value="Baru">Baru</option>
                                <option value="Lama">Lama</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- === KOLOM KANAN: DETAIL JANJI TEMU === --}}
            <div class="col-lg-6 mb-4">
                <div class="form-group-section h-100">
                    <h4 class="mb-4 text-gold border-bottom border-secondary pb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-calendar-alt fs-4"></i> Detail Janji Temu
                    </h4>
                    
                    <div class="row">
                        <div class="form-group col-md-6 mb-3">
                            <label for="poli" class="text-muted small mb-1">Poli</label>
                            <select class="form-control" id="poli" name="poli" required>
                                <option value="" class="text-muted">Pilih Poli</option>
                                @if(isset($polis))
                                    @foreach($polis as $poli)
                                        <option value="{{ $poli->kode_poli }}">{{ $poli->nama_poli }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label for="dokter" class="text-muted small mb-1">Dokter</label>
                            <select class="form-control" id="dokter" name="dokter" required>
                                <option value="" class="text-muted">Pilih Dokter</option>
                                @if(isset($dokters))
                                    @foreach($dokters as $dokter)
                                        <option value="{{ $dokter->kode_dokter }}">{{ $dokter->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- Tanggal & Waktu dengan ICON --}}
                    <div class="row">
                        <div class="form-group col-md-6 mb-3">
                            <label for="tanggal_janji" class="text-muted small mb-1">Tanggal</label>
                            <div class="input-group" id="flatpickr-tanggal">
                                <input type="text" class="form-control date-picker has-icon-right" id="tanggal_janji" name="tanggal_janji" placeholder="Pilih Tanggal..." required data-input>
                                <div class="input-group-append">
                                     <span class="input-group-text-custom">
                                         <i class="fas fa-calendar-alt" style="font-size: 1.1rem;"></i>
                                     </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label for="waktu_janji" class="text-muted small mb-1">Jam (WIB)</label>
                            <div class="input-group">
                                <input type="text" class="form-control has-icon-right" id="waktu_janji" name="waktu_janji" placeholder="00:00" required>
                                <div class="input-group-append">
                                     <span class="input-group-text-custom">
                                         <i class="fas fa-clock" style="font-size: 1.1rem;"></i>
                                     </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="keluhan" class="text-muted small mb-1">Keluhan</label>
                        <textarea class="form-control" id="keluhan" name="keluhan" rows="4" placeholder="Tulis keluhan pasien di sini..."></textarea>
                    </div>

                    {{-- Info Penting --}}
                    <div class="callout callout-gold mt-4 mb-0">
                        <h5 class="text-gold" style="font-size: 1rem; font-weight: bold;">
                            <i class="fas fa-info-circle me-1"></i> Informasi Penting
                        </h5>
                        <p class="text-muted small mb-0">
                             Pastikan jadwal dokter sudah terkonfirmasi. Pasien disarankan hadir 15 menit sebelum jam layanan dimulai untuk administrasi ulang.
                        </p>
                    </div>
                </div>
            </div>

        </div> 

        {{-- === ROW BAWAH: PEMBAYARAN === --}}
        <div class="row">
            <div class="col-12 mb-4">
                <div class="form-group-section">
                    <h4 class="mb-4 text-gold border-bottom border-secondary pb-2 d-flex align-items-center gap-2">
                        <i class="fas fa-receipt fs-4"></i> Informasi Pembayaran
                    </h4>
                    <div class="row align-items-end">
                        <div class="form-group col-md-4 mb-3">
                            <label class="text-muted small mb-1">Metode Pembayaran</label>
                            <select class="form-control" id="metode_bayar" name="metode_bayar" required>
                                <option value="Manual">Manual</option>
                                <option value="Cash">Cash</option>
                                <option value="Transfer">Transfer</option>
                                <option value="Midtrans">Midtrans</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <label class="text-muted small mb-1">Status Pembayaran</label>
                            <select class="form-control" id="status_bayar" name="status_bayar" required>
                                <option value="belum_bayar">Belum Bayar</option>
                                <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                                <option value="lunas_online">Lunas (Online)</option>
                                <option value="lunas_manual">Lunas (Manual)</option>
                                <option value="gagal">Gagal</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4 mb-3">
                            <label class="text-muted small mb-1">Total Biaya (Rp)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-group-text-rp">Rp</span>
                                </div>
                                <input type="number" class="form-control input-biaya" id="total_biaya" name="total_biaya" value="25000">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-footer d-flex justify-content-end gap-3 pb-5">
            <a href="{{ route('reservasi.admin.index') }}" class="btn btn-secondary-dark px-4">Batal</a>
            <button type="submit" class="btn btn-gold px-4 shadow-sm">
                <i class="fas fa-check-circle me-2"></i> Buat Reservasi
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
        
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",
            minDate: "today",
            wrap: true
        });

        const btnSearch = document.getElementById('btn_search_pasien');
        const btnReset = document.getElementById('btn_reset_pasien');
        const searchInput = document.getElementById('search_pasien');
        const pasienIdExistInput = document.getElementById('pasien_id_exist');
        const pasienStatus = document.getElementById('pasien_status');
        const jenisPasienInput = document.getElementById('jenis_pasien');
        
        const formPasienFields = [
            document.getElementById('nama_lengkap'),
            document.getElementById('ttl'),
            document.getElementById('alamat'),
            document.getElementById('no_hp'),
            jenisPasienInput
        ];

        function togglePasienFields(isReadOnly, clearValues = false) {
            formPasienFields.forEach(field => {
                if (clearValues) field.value = '';
                field.readOnly = isReadOnly;
            });
            if(!isReadOnly) {
                 pasienIdExistInput.value = '';
                 pasienStatus.innerHTML = 'Mode Input Pasien Baru aktif.';
                 pasienStatus.classList.remove('text-success', 'text-danger', 'text-warning');
                 pasienStatus.classList.add('text-muted');
            }
        }

        btnSearch.addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query.length < 3) {
                pasienStatus.textContent = 'Masukkan minimal 3 karakter untuk mencari.';
                pasienStatus.classList.add('text-warning');
                return;
            }
            pasienStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari...';

            fetch("{{ route('reservasi.admin.cariPasien') }}?q=" + query)
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        const pasien = data[0];
                        document.getElementById('nama_lengkap').value = pasien.nama_lengkap || '';
                        document.getElementById('ttl').value = pasien.tanggal_lahir || '';
                        document.getElementById('alamat').value = pasien.alamat_rumah || '';
                        document.getElementById('no_hp').value = pasien.nomor_telepon || '';
                        
                        jenisPasienInput.value = 'Lama'; 
                        
                        pasienIdExistInput.value = pasien.id_database;
                        togglePasienFields(true, false);
                        pasienStatus.innerHTML = `Pasien Ditemukan: <strong class="text-gold">${pasien.nomor_rm} - ${pasien.nama_lengkap}</strong>. Form dikunci.`;
                        pasienStatus.classList.remove('text-muted', 'text-warning', 'text-danger');
                        pasienStatus.classList.add('text-success');
                    } else {
                        togglePasienFields(false, true);
                        jenisPasienInput.value = 'Baru';
                        pasienStatus.textContent = 'Pasien tidak ditemukan. Silakan input data baru secara manual.';
                        pasienStatus.classList.remove('text-muted', 'text-success');
                        pasienStatus.classList.add('text-warning');
                    }
                })
                .catch(error => {
                    pasienStatus.textContent = 'Terjadi kesalahan jaringan.';
                    pasienStatus.classList.add('text-danger');
                    togglePasienFields(false, false);
                });
        });

        btnReset.addEventListener('click', function() {
            searchInput.value = '';
            togglePasienFields(false, true);
            jenisPasienInput.value = 'Baru';
        });

        searchInput.addEventListener('input', function() {
            if (this.value.trim().length === 0 && pasienIdExistInput.value !== '') {
                togglePasienFields(false, false);
            }
        });

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
                if (!response.ok) return response.json().then(err => { throw new Error(err.message || 'Gagal'); });
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success', title: 'Berhasil!', text: data.message,
                        background: '#1e1e2d', color: '#fff', confirmButtonColor: '#D4AF37'
                    }).then(() => { window.location.href = "{{ route('reservasi.admin.index') }}"; });
                } else {
                    Swal.fire({
                        icon: 'error', title: 'Gagal', text: data.message,
                        background: '#1e1e2d', color: '#fff', confirmButtonColor: '#D4AF37'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error', title: 'Gagal', text: error.message,
                    background: '#1e1e2d', color: '#fff', confirmButtonColor: '#D4AF37'
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