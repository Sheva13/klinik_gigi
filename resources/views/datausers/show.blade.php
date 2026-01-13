@extends('layouts.adminlte')

@section('title', 'Detail User')

@section('content')

<div class="container-fluid">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-dark border-secondary d-flex align-items-center p-2 rounded-circle">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <span class="text-secondary small d-block">Detail User</span>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h1 class="h3 fw-bold mb-0 text-white">{{ $user->nama_pengguna }}</h1>
                
                {{-- BADGE NO RM (BARU DITAMBAHKAN) --}}
                @if($user->rekamMedis)
                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary d-flex align-items-center">
                        <span class="material-symbols-outlined fs-6 me-1">id_card</span>
                        {{ $user->rekamMedis->rekam_medis }}
                    </span>
                @endif

                {{-- BADGE STATUS VERIFIKASI --}}
                @if($user->rekamMedis && ($user->rekamMedis->verifikasi ?? 0) == 1)
                    <span class="badge bg-gold text-dark d-flex align-items-center" title="Akun Terverifikasi">
                        <span class="material-symbols-outlined fs-6 me-1">verified</span> Valid
                    </span>
                @else
                    <span class="badge bg-secondary d-flex align-items-center" title="Belum Verifikasi KTP">
                        <span class="material-symbols-outlined fs-6 me-1">warning</span> Unverified
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="row align-items-stretch">
        <div class="col-lg-8 d-flex flex-column gap-4">
            
            {{-- KARTU INFORMASI PENGGUNA --}}
            <div class="card card-dark-premium border-0 mb-0">
                <div class="card-header border-bottom border-secondary py-3">
                    <div class="d-flex align-items-center">
                        <span class="material-symbols-outlined text-warning me-2">person</span>
                        <h5 class="mb-0 text-white fw-bold">Informasi Pengguna</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                                    <span class="material-symbols-outlined">person</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Nama Akun</span>
                                    <div class="text-white fw-bold">{{ $user->nama_pengguna }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                    <span class="material-symbols-outlined">badge</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">NIK (Akun)</span>
                                    <div class="text-white fw-bold">{{ $user->nik ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                    <span class="material-symbols-outlined">email</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Email</span>
                                    <div class="text-white fw-bold">{{ $user->email ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger me-3">
                                    <span class="material-symbols-outlined">phone</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">No HP</span>
                                    <div class="text-white fw-bold">{{ $user->no_hp ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-4">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                    <span class="material-symbols-outlined">home</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Alamat (Akun)</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->alamat ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KARTU REKAM MEDIS --}}
            @if($user->rekamMedis)
            <div class="card card-dark-premium border-0 mb-0">
                <div class="card-header border-bottom border-secondary py-3">
                    <div class="d-flex align-items-center">
                        <span class="material-symbols-outlined text-info me-2">medical_information</span>
                        <h5 class="mb-0 text-white fw-bold">Rekam Medis</h5>
                    </div>
                </div>

                <div class="card-body p-4">
                    
                    {{-- NO REKAM MEDIS DITAMBAHKAN DI SINI (PALING BESAR) --}}
                    <div class="row mb-4 pb-3 border-bottom border-secondary">
                        <div class="col-12">
                             <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary bg-opacity-25 text-primary me-3 border border-primary" style="width: 60px; height: 60px;">
                                    <span class="material-symbols-outlined fs-2">id_card</span>
                                </div>
                                <div>
                                    <span class="info-label text-gold small text-uppercase fw-bold letter-spacing-1">Nomor Rekam Medis</span>
                                    <div class="text-white fw-bold fs-3">{{ $user->rekamMedis->rekam_medis }}</div>
                                    <div class="small text-muted">Terdaftar sejak: {{ \Carbon\Carbon::parse($user->rekamMedis->created_at)->format('d F Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                                    <span class="material-symbols-outlined">home</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Alamat Domisili</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->alamat ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-secondary bg-opacity-10 text-secondary me-3">
                                    <span class="material-symbols-outlined">work</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Pekerjaan</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->pekerjaan ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                    <span class="material-symbols-outlined">people</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Status Pernikahan</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->status_nikah ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                                    <span class="material-symbols-outlined">phone</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">No HP Aktif</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->hp ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger me-3">
                                    <span class="material-symbols-outlined">bloodtype</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Golongan Darah</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->golongan_darah ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                    <span class="material-symbols-outlined">person</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Nama Wali</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->nama_wali ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                    <span class="material-symbols-outlined">family_restroom</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Hubungan Wali</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->hubungan_wali ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                                    <span class="material-symbols-outlined">phone</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">HP Wali</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->hp_wali ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                    <span class="material-symbols-outlined">person_2</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Jenis Pasien</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->jenis_pasien ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger me-3">
                                    <span class="material-symbols-outlined">confirmation_number</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">No Peserta</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->no_peserta ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-4">
                        <div class="col-md-12">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                                    <span class="material-symbols-outlined">health_and_safety</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Nama Asuransi</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->nama_asuransi ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BAGIAN DATA SENSITIF (Jika ada isinya) --}}
                    @if($user->rekamMedis->no_identitas || $user->rekamMedis->tipe_identitas || $user->rekamMedis->tempat_lahir)
                    <hr class="my-4 border-secondary">

                    <div class="d-flex align-items-center mb-3">
                        <span class="material-symbols-outlined text-warning me-2">lock</span>
                        <h5 class="mb-0 text-white fw-bold">Data Sensitif</h5>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-danger bg-opacity-10 text-danger me-3">
                                    <span class="material-symbols-outlined">badge</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">No Identitas</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->no_identitas ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-secondary bg-opacity-10 text-secondary me-3">
                                    <span class="material-symbols-outlined">badge</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Tipe Identitas</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->tipe_identitas ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                    <span class="material-symbols-outlined">location_on</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Tempat Lahir</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->tempat_lahir ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                    <span class="material-symbols-outlined">calendar_today</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Tanggal Lahir (RM)</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->tanggal_lahir ? \Carbon\Carbon::parse($user->rekamMedis->tanggal_lahir)->format('d F Y') : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-secondary bg-opacity-10 text-secondary me-3">
                                    <span class="material-symbols-outlined">man</span>
                                </div>
                                <div>
                                    <span class="info-label text-secondary small">Jenis Kelamin (RM)</span>
                                    <div class="text-white fw-bold">
                                        {{ $user->rekamMedis->jenis_kelamin == 'L' ? 'Laki-laki' : ($user->rekamMedis->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card card-dark-premium border-0 h-100">
                <div class="card-header border-bottom border-secondary py-3">
                    <div class="d-flex align-items-center">
                        <span class="material-symbols-outlined text-gold me-2">info</span>
                        <h5 class="mb-0 text-white fw-bold">Aksi</h5>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-grid gap-3">
                        <a href="{{ route('admin.users.edit', $user->user_id) }}" class="btn btn-gold btn-lg d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined">edit</span> Edit User
                        </a>
                        <a href="{{ route('admin.users.sensitive.edit', $user->user_id) }}" class="btn btn-outline-warning border-warning btn-lg d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined">lock</span> Edit Data Sensitif
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary-dark btn-lg d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined">arrow_back</span> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection