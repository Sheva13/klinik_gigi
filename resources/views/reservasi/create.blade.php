@extends('layouts.adminlte')

@section('title', 'Tambah Reservasi Baru')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-gold font-weight-bold">Tambah Reservasi Baru</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#" class="text-gold">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reservasi.admin.index') }}" class="text-gold">Reservasi</a></li>
                <li class="breadcrumb-item active">Tambah Baru</li>
            </ol>
        </div>
    </div>
@stop

@section('content')

{{-- CSS Custom Tetap Sama --}}
<style>
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --dark-card: #1e1e2d;
        --dark-input: #151521;
        --border-color: #2b2b40;
    }
    
    .text-gold { color: var(--gold-primary) !important; }
    
    .custom-dark-card {
        background-color: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        color: #fff;
    }
    .card-header {
        border-bottom: 1px solid var(--border-color);
    }

    .form-control-dark {
        background-color: var(--dark-input);
        border: 1px solid var(--border-color);
        color: #fff;
    }
    .form-control-dark:focus {
        background-color: var(--dark-input);
        border-color: var(--gold-primary);
        color: #fff;
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
    }
    .form-control-dark::placeholder {
        color: #6c757d;
    }

    select.form-control-dark option {
        background-color: var(--dark-card);
        color: #fff;
    }

    .btn-gold {
        background-color: var(--gold-primary);
        color: #000;
        font-weight: 600;
        border: none;
    }
    .btn-gold:hover {
        background-color: var(--gold-hover);
        color: #000;
    }
    .btn-secondary-dark {
        background-color: #343a40;
        color: #fff;
        border: 1px solid #6c757d;
    }
    .btn-secondary-dark:hover {
        background-color: #23272b;
        color: #fff;
    }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card custom-dark-card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-gold"><i class="fas fa-file-medical mr-2"></i>Form Reservasi</h3>
                </div>
                
                {{-- Form Mulai --}}
                <form id="formManual"> 
                    @csrf
                    <div class="card-body">
                        
                        {{-- Baris 1: Data Pasien --}}
                        <div class="form-group">
                            <label class="text-muted small">Cari Pasien (Nama / No RM)</label>
                            {{-- Dropdown Dinamis dari Database --}}
                            <select name="pasien_id" class="form-control form-control-dark select2" required>
                                <option selected disabled value="">-- Pilih Pasien --</option>
                                @foreach($pasiens as $pasien)
                                    <option value="{{ $pasien->rekam_medis }}">
                                        {{ $pasien->rekam_medis }} - {{ $pasien->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted mt-1 d-block">
                                Pasien belum terdaftar? <a href="#" class="text-gold">Tambah Pasien Baru</a>
                            </small>
                        </div>

                        <div class="row">
                            {{-- Baris 2: Poli & Dokter --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Poli Tujuan</label>
                                    {{-- Dropdown Dinamis Poli --}}
                                    <select name="poli_id" class="form-control form-control-dark" required>
                                        <option selected disabled value="">-- Pilih Poli --</option>
                                        @foreach($polis as $poli)
                                            <option value="{{ $poli->kode_poli }}">
                                                {{ $poli->nama_poli }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Dokter</label>
                                    {{-- Dropdown Dinamis Dokter --}}
                                    <select name="dokter_id" class="form-control form-control-dark" required>
                                        <option selected disabled value="">-- Pilih Dokter --</option>
                                        @foreach($dokters as $dokter)
                                            <option value="{{ $dokter->kode_dokter }}">
                                                {{ $dokter->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Baris 3: Tanggal & Waktu --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Tanggal Reservasi</label>
                                    <input type="date" name="tanggal_pesan" class="form-control form-control-dark" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Jadwal Praktek</label>
                                    {{-- Dropdown Dinamis Jadwal (Pengganti Input Manual) --}}
                                    <select name="jadwal_id" class="form-control form-control-dark" required>
                                        <option selected disabled value="">-- Pilih Jadwal --</option>
                                        @foreach($jadwals as $jadwal)
                                            <option value="{{ $jadwal->id }}">
                                                {{ $jadwal->hari }} ({{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Baris 4: Keluhan --}}
                        <div class="form-group">
                            <label class="text-muted small">Keluhan Utama</label>
                            <textarea class="form-control form-control-dark" rows="3" placeholder="Contoh: Sakit gigi geraham bawah sejak 2 hari lalu..."></textarea>
                        </div>

                        {{-- Baris 5: Status Awal --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Status Reservasi</label>
                                    <select class="form-control form-control-dark" disabled>
                                        <option value="waiting" selected>Menunggu (Default)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Metode Pembayaran (Opsional)</label>
                                    <select class="form-control form-control-dark" disabled>
                                        <option value="verified" selected>Langsung Lunas (Default)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="card-footer bg-transparent border-top border-secondary">
                        <button type="submit" class="btn btn-gold px-4 mr-2" id="btnSave"><i class="fas fa-save mr-1"></i> Simpan Data</button>
                        <a href="{{ route('reservasi.admin.index') }}" class="btn btn-secondary-dark px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('formManual').addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        let formData = new FormData(this);
        let btnSave = document.getElementById('btnSave');
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
            btnSave.innerHTML = '<i class="fas fa-save mr-1"></i> Simpan Data';
            btnSave.disabled = false;
        });
    });
</script>
@stop