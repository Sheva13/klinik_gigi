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
                <li class="breadcrumb-item"><a href="{{ url('/reservasi') }}" class="text-gold">Reservasi</a></li>
                <li class="breadcrumb-item active">Tambah Baru</li>
            </ol>
        </div>
    </div>
@stop

@section('content')

{{-- CSS Custom yang sama dengan Index biar konsisten --}}
<style>
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --dark-card: #1e1e2d;
        --dark-input: #151521;
        --border-color: #2b2b40;
    }
    
    .text-gold { color: var(--gold-primary) !important; }
    
    /* Card Styling */
    .custom-dark-card {
        background-color: var(--dark-card);
        border: 1px solid var(--border-color);
        color: #fff;
    }
    .card-header {
        border-bottom: 1px solid var(--border-color);
    }

    /* Form Input Styling */
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

    /* Select Option Styling */
    select.form-control-dark option {
        background-color: var(--dark-card);
        color: #fff;
    }

    /* Button Styling */
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
                <form action="#" method="POST"> 
                    @csrf
                    <div class="card-body">
                        
                        {{-- Baris 1: Data Pasien --}}
                        <div class="form-group">
                            <label class="text-muted small">Cari Pasien (Nama / No RM)</label>
                            <select class="form-control form-control-dark select2">
                                <option selected disabled>-- Pilih Pasien --</option>
                                <option value="1">RM001 - Budi Santoso</option>
                                <option value="2">RM002 - Siti Aminah</option>
                                <option value="3">RM003 - Joko Anwar</option>
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
                                    <select class="form-control form-control-dark">
                                        <option selected disabled>-- Pilih Poli --</option>
                                        <option>Poli Umum</option>
                                        <option>Poli Gigi</option>
                                        <option>Poli Anak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Dokter</label>
                                    <select class="form-control form-control-dark">
                                        <option selected disabled>-- Pilih Dokter --</option>
                                        <option>drg. Aprilia Puspita Anda</option>
                                        <option>dr. Bambang</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Baris 3: Tanggal & Waktu --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Tanggal Reservasi</label>
                                    <input type="date" class="form-control form-control-dark">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Jam Estimasi</label>
                                    <input type="time" class="form-control form-control-dark">
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
                                    <select class="form-control form-control-dark">
                                        <option value="menunggu" selected>Menunggu</option>
                                        <option value="terkonfirmasi">Terkonfirmasi</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted small">Metode Pembayaran (Opsional)</label>
                                    <select class="form-control form-control-dark">
                                        <option value="cash">Tunai / Cash</option>
                                        <option value="transfer">Transfer Bank</option>
                                        <option value="bpjs">BPJS</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="card-footer bg-transparent border-top border-secondary">
                        <button type="submit" class="btn btn-gold px-4 mr-2"><i class="fas fa-save mr-1"></i> Simpan Data</button>
                        <a href="{{ url('/reservasi') }}" class="btn btn-secondary-dark px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop