@extends('layouts.adminlte')

@section('title', 'Edit Data Sensitif')

@section('content')

<div class="container-fluid px-0">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.users.edit', $user->user_id) }}" class="btn btn-secondary-dark rounded-circle p-2" style="width: 45px; height: 45px; justify-content: center;">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Edit Data <span class="text-gold">Sensitif</span></h1>
            <p class="text-muted mb-0">Perbarui informasi pribadi penting pengguna & pasien.</p>
        </div>
    </div>

    <div class="edit-card">
        <div class="mb-4 border-bottom border-secondary pb-3">
            <h4 class="fw-bold mb-1">Konfirmasi Identitas</h4>
            <p class="text-muted mb-0">User: <span class="text-gold fw-bold">{{ $user->nama_pengguna ?? 'Nama User' }}</span></p>
        </div>

        <form method="POST" action="{{ route('admin.users.sensitive.update', $user->user_id) }}">
            @csrf
            @method('PUT')

            <div class="alert alert-warning border-warning bg-opacity-10" role="alert">
                <div class="d-flex align-items-center">
                    <span class="material-symbols-outlined text-warning me-2">warning</span>
                    <div>
                        <h6 class="alert-heading mb-1">Perhatian!</h6>
                        <p class="mb-0">Perubahan data sensitif akan dicatat dalam log audit. Harap cantumkan alasan perubahan yang jelas.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="nik" class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-dark" id="nik" name="nik"
                               value="{{ old('nik', $user->nik) }}" required maxlength="20">
                        <div class="form-text">Nomor Induk Kependudukan</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-dark" id="tanggal_lahir" name="tanggal_lahir"
                               value="{{ old('tanggal_lahir', $user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('Y-m-d') : '') }}" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select form-select-dark" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control form-control-dark" id="tempat_lahir" name="tempat_lahir"
                               value="{{ old('tempat_lahir', $user->rekamMedis->tempat_lahir ?? '') }}" maxlength="100">
                    </div>
                </div>
            </div>

            @if($user->rekamMedis)
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="no_identitas" class="form-label">No Identitas (RM)</label>
                        <input type="text" class="form-control form-control-dark" id="no_identitas" name="no_identitas"
                               value="{{ old('no_identitas', $user->rekamMedis->no_identitas ?? '') }}" maxlength="30">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="tipe_identitas" class="form-label">Tipe Identitas</label>
                        <input type="text" class="form-control form-control-dark" id="tipe_identitas" name="tipe_identitas"
                               value="{{ old('tipe_identitas', $user->rekamMedis->tipe_identitas ?? '') }}" maxlength="20">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="rm_tanggal_lahir" class="form-label">Tanggal Lahir (Rekam Medis) <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-dark" id="rm_tanggal_lahir" name="rm_tanggal_lahir"
                               value="{{ old('rm_tanggal_lahir', $user->rekamMedis->tanggal_lahir ? \Carbon\Carbon::parse($user->rekamMedis->tanggal_lahir)->format('Y-m-d') : '') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="rm_jenis_kelamin" class="form-label">Jenis Kelamin (Rekam Medis) <span class="text-danger">*</span></label>
                        <select class="form-select form-select-dark" id="rm_jenis_kelamin" name="rm_jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('rm_jenis_kelamin', $user->rekamMedis->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('rm_jenis_kelamin', $user->rekamMedis->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card bg-warning bg-opacity-10 border border-warning mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <span class="material-symbols-outlined text-warning me-3 fs-2">verified_user</span>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold text-warning mb-1">Verifikasi Data Medis</h5>
                            <p class="text-white small mb-2">
                                Apakah dokumen fisik (KTP/KK) pasien sudah sesuai dengan data di atas?
                                <br>Jika ya, aktifkan tombol di bawah ini.
                            </p>
                            
                            <div class="form-check form-switch">
                                {{-- Hidden input kirim nilai 0 kalau switch mati --}}
                                <input type="hidden" name="verifikasi" value="0">
                                
                                <input class="form-check-input" type="checkbox" role="switch" id="verifikasi" name="verifikasi" value="1" 
                                    style="width: 3em; height: 1.5em;"
                                    {{ ($user->rekamMedis->verifikasi ?? 0) == 1 ? 'checked' : '' }}>
                                <label class="form-check-label ms-2 align-middle fw-bold text-white mt-1" for="verifikasi">
                                    DATA TERVERIFIKASI (VALID)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="mb-4">
                <label for="alasan" class="form-label">Alasan Perubahan <span class="text-danger">*</span></label>
                <textarea class="form-control form-control-dark" id="alasan" name="alasan" rows="4" required
                          placeholder="Jelaskan alasan Anda melakukan perubahan data sensitif ini. Minimal 10 karakter.">{{ old('alasan') }}</textarea>
                <div class="form-text">Alasan ini akan dicatat dalam log audit untuk keperluan pelacakan dan keamanan.</div>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-gold px-4 fw-bold">
                    <span class="material-symbols-outlined align-middle">save</span> Simpan Perubahan Sensitif
                </button>
                <a href="{{ route('admin.users.edit', $user->user_id) }}" class="btn btn-secondary-dark px-4 fw-bold">
                    <span class="material-symbols-outlined align-middle">close</span> Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection