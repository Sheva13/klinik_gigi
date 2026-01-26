@extends('layouts.adminlte')

@section('title', 'Detail Pembayaran Reservasi')

@section('content')

<div class="container-fluid px-0">

    {{-- HEADER WITH ADMIN INFO --}}
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Detail <span class="text-gold">Pembayaran</span></h1>
            <p class="text-muted mb-0">Informasi lengkap tentang pembayaran reservasi pasien</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                {{-- Data Admin --}}
                <div class="fw-bold text-white">{{ Auth::guard('admin')->user()->nama ?? 'Admin' }}</div>
                <small class="text-muted">Administrator</small>
            </div>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}"
                 alt="Profile"
                 class="rounded-circle border border-secondary"
                 style="width: 45px; height: 45px; object-fit: cover;">
        </div>
    </div>
    {{-- END HEADER --}}


    {{-- MAIN CONTENT CARD --}}
    <div class="edit-card">
        
        {{-- 1. RINGKASAN RESERVASI --}}
        <div class="mb-5">
            <h4 class="fw-bold mb-3 text-white">Ringkasan Reservasi</h4>

            <div class="row text-white g-3">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4 text-muted">Nama Pasien</dt>
                        <dd class="col-sm-8 fw-bold">{{ $reservasi->rekamMedis->nama ?? 'Nama Pasien' }}</dd>

                        <dt class="col-sm-4 text-muted">Nomor Rekam Medis</dt>
                        <dd class="col-sm-8 fw-bold text-gold">{{ $reservasi->rekamMedis->rekam_medis ?? 'RM000' }}</dd>

                        <dt class="col-sm-4 text-muted">Dokter</dt>
                        <dd class="col-sm-8">{{ $reservasi->dokter->nama ?? '-' }}</dd>

                        <dt class="col-sm-4 text-muted">Poliklinik</dt>
                        <dd class="col-sm-8">{{ $reservasi->dokter->poli->nama_poli ?? '-' }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4 text-muted">Tanggal Pesan</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($reservasi->tanggal_pesan)->format('d F Y') }}</dd>

                        <dt class="col-sm-4 text-muted">Tanggal Periksa</dt>
                        <dd class="col-sm-8">{{ \Carbon\Carbon::parse($reservasi->tanggal_periksa)->format('d F Y') }}</dd>

                        <dt class="col-sm-4 text-muted">Jam Periksa</dt>
                        <dd class="col-sm-8">{{ $reservasi->jadwal->jam_mulai ?? '-' }} - {{ $reservasi->jadwal->jam_selesai ?? '-' }}</dd>

                        <dt class="col-sm-4 text-muted">Status Reservasi</dt>
                        <dd class="col-sm-8">
                            @php
                                $status = $reservasi->status_reservasi ?? 'menunggu';
                                $label = match($status) {
                                    'menunggu' => 'Menunggu',
                                    'proses' => 'Sedang Diproses',
                                    'selesai' => 'Selesai',
                                    'batal' => 'Dibatalkan',
                                    default => ucfirst(str_replace('_', ' ', $status))
                                };
                                $class = match($status) {
                                    'menunggu' => 'bg-warning-soft text-dark',
                                    'proses' => 'bg-info-soft',
                                    'selesai' => 'bg-success-soft',
                                    'batal' => 'bg-danger-soft',
                                    default => 'bg-secondary-soft'
                                };
                            @endphp
                            <span class="badge {{ $class }} py-2 px-3 fw-bold">{{ $label }}</span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- 2. DETAIL PEMBAYARAN --}}
        <div class="mb-5 pt-3 border-top border-secondary">
            <h4 class="fw-bold mb-3 text-white">Detail Pembayaran</h4>
            
            <div class="row text-white g-3">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4 text-muted">Status Pembayaran</dt>
                        <dd class="col-sm-8">
                            @php
                                $status = $reservasi->status_pembayaran ?? 'menunggu_verifikasi';
                                $label = match($status) {
                                    'lunas', 'terverifikasi' => 'Lunas',
                                    'gagal' => 'Gagal',
                                    'menunggu_verifikasi' => 'Menunggu Verifikasi',
                                    default => 'Belum Lunas'
                                };
                                $class = match($status) {
                                    'lunas', 'terverifikasi' => 'bg-success-soft',
                                    'gagal' => 'bg-danger-soft',
                                    'menunggu_verifikasi' => 'bg-warning-soft text-dark',
                                    default => 'bg-secondary-soft'
                                };
                            @endphp
                            <span class="badge {{ $class }} py-2 px-3 fw-bold">{{ $label }}</span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Jumlah Total</dt>
                        <dd class="col-sm-8">
                            <h4 class="text-white fw-bold">Rp {{ number_format($reservasi->pembayaran_total ?? 0, 0, ',', '.') }}</h4>
                        </dd>

                        <dt class="col-sm-4 text-muted">Metode Pembayaran</dt>
                        <dd class="col-sm-8">{{ $reservasi->metode_pembayaran ?? 'Tidak Ditentukan' }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4 text-muted">Tanggal Pembayaran</dt>
                        <dd class="col-sm-8">{{ $reservasi->tanggal_pembayaran ? \Carbon\Carbon::parse($reservasi->tanggal_pembayaran)->format('d F Y H:i') : '-' }}</dd>

                        <dt class="col-sm-4 text-muted">Catatan</dt>
                        <dd class="col-sm-8">{{ $reservasi->catatan_pembayaran ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- 3. BUKTI PEMBAYARAN --}}
        <div class="mb-5 pt-3 border-top border-secondary">
            <h4 class="fw-bold mb-3 text-white">Bukti Pembayaran</h4>
            
            @if($reservasi->bukti_pembayaran_path ?? null)
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-dark text-white">
                            <div class="card-body">
                                <h6 class="card-title text-gold">File Bukti Pembayaran</h6>
                                <p class="card-text">{{ $reservasi->bukti_pembayaran_file_name ?? 'Bukti Pembayaran' }}</p>
                                
                                <div class="mt-3">
                                    <a href="{{ Storage::url($reservasi->bukti_pembayaran_path) }}" 
                                       target="_blank" 
                                       class="btn btn-outline-gold btn-sm">
                                        <i class="fas fa-eye"></i> Lihat Bukti Pembayaran
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Belum ada bukti pembayaran yang diunggah untuk reservasi ini.
                </div>
            @endif
        </div>

        {{-- 4. FORM VERIFIKASI PEMBAYARAN --}}
        <div class="mb-5 pt-3 border-top border-secondary">
            <h4 class="fw-bold mb-3 text-white">Verifikasi Pembayaran</h4>
            
            <form action="{{ route('reservasi.admin.updatePembayaran', $reservasi->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-white">Status Verifikasi</label>
                        <select name="status_pembayaran" class="form-select bg-dark text-white border-secondary">
                            <option value="terverifikasi" {{ ($reservasi->status_pembayaran ?? '') == 'terverifikasi' ? 'selected' : '' }}>
                                Terverifikasi (Lunas)
                            </option>
                            <option value="gagal" {{ ($reservasi->status_pembayaran ?? '') == 'gagal' ? 'selected' : '' }}>
                                Gagal Verifikasi
                            </option>
                        </select>
                        <div class="form-text text-muted">
                            Pilih status verifikasi pembayaran untuk pasien ini.
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('reservasi.admin.index') }}" class="btn btn-secondary-dark">
                        Kembali ke Daftar
                    </a>
                    
                    <button type="submit" class="btn btn-gold">
                        <i class="fas fa-save"></i> Simpan Verifikasi
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>

@endsection