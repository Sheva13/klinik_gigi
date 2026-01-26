@extends('layouts.adminlte')

@section('title', 'Edit User')

@section('content')

<div class="container-fluid px-0">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary-dark rounded-circle p-2" style="width: 45px; height: 45px; justify-content: center;">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Edit Data <span class="text-gold">User</span></h1>
            <p class="text-muted mb-0">Perbarui informasi pengguna & pasien (non-sensitif).</p>
        </div>
    </div>

    <div class="d-flex gap-4 mb-4">
        <div class="flex-fill">
            <div class="edit-card">
                <div class="mb-4 border-bottom border-secondary pb-3">
                    <h4 class="fw-bold mb-1">Data Pengguna</h4>
                    <p class="text-muted mb-0">Informasi dasar pengguna (non-sensitif)</p>
                </div>

                <form method="POST" action="{{ route('admin.users.update',$user->user_id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="nama_pengguna" class="form-label">Nama</label>
                                <input type="text" class="form-control form-control-dark" id="nama_pengguna" name="nama_pengguna"
                                       value="{{ old('nama_pengguna',$user->nama_pengguna) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control form-control-dark" id="email" name="email"
                                       value="{{ old('email',$user->email) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="no_hp" class="form-label">No HP</label>
                        <input type="text" class="form-control form-control-dark" id="no_hp" name="no_hp"
                               value="{{ old('no_hp',$user->no_hp) }}">
                    </div>

                    <div class="mb-4">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control form-control-dark" id="alamat" name="alamat" rows="3">{{ old('alamat',$user->alamat) }}</textarea>
                    </div>
            </div>
        </div>

        <div class="flex-fill" style="max-width: 350px;">
            <div class="card card-dark-premium border-0 h-100">
                <div class="card-header border-bottom border-secondary py-3">
                    <div class="d-flex align-items-center">
                        <span class="material-symbols-outlined text-gold me-2">lock</span>
                        <h5 class="mb-0 text-white fw-bold">Data Sensitif</h5>
                    </div>
                </div>

                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-grid gap-3">
                        <div class="text-center mb-3">
                            <span class="material-symbols-outlined text-warning fs-1">shield</span>
                            <p class="text-muted mt-2">Data pribadi penting seperti NIK, tanggal lahir, dan jenis kelamin</p>
                        </div>

                        <a href="{{ route('admin.users.sensitive.edit', $user->user_id) }}" class="btn btn-outline-warning border-warning d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined">edit</span> Edit Data Sensitif
                        </a>
                        <a href="{{ route('admin.users.show', $user->user_id) }}" class="btn btn-secondary-dark d-flex align-items-center justify-content-center gap-2">
                            <span class="material-symbols-outlined">visibility</span> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($user->rekamMedis)
    <div class="edit-card">
        <div class="mb-4 border-bottom border-secondary pb-3">
            <h4 class="fw-bold mb-1">Data Rekam Medis (Non-Sensitif)</h4>
            <p class="text-muted mb-0">Informasi medis pasien (non-sensitif)</p>
        </div>

        <!-- Gabungkan form rekam medis ke dalam form utama -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="status_nikah" class="form-label">Status Pernikahan</label>
                    <select class="form-select form-select-dark" id="status_nikah" name="status_nikah">
                        <option value="">Pilih Status</option>
                        <option value="Belum Kawin" {{ old('status_nikah', $user->rekamMedis->status_nikah) == 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                        <option value="Kawin" {{ old('status_nikah', $user->rekamMedis->status_nikah) == 'Kawin' ? 'selected' : '' }}>Kawin</option>
                        <option value="Cerai Hidup" {{ old('status_nikah', $user->rekamMedis->status_nikah) == 'Cerai Hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                        <option value="Cerai Mati" {{ old('status_nikah', $user->rekamMedis->status_nikah) == 'Cerai Mati' ? 'selected' : '' }}>Cerai Mati</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="pekerjaan" class="form-label">Pekerjaan</label>
                    <input type="text" class="form-control form-control-dark" id="pekerjaan" name="pekerjaan"
                           value="{{ old('pekerjaan',$user->rekamMedis->pekerjaan) }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="hp" class="form-label">No HP</label>
                    <input type="text" class="form-control form-control-dark" id="hp" name="hp"
                           value="{{ old('hp',$user->rekamMedis->hp) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="golongan_darah" class="form-label">Golongan Darah</label>
                    <input type="text" class="form-control form-control-dark" id="golongan_darah" name="golongan_darah"
                           value="{{ old('golongan_darah',$user->rekamMedis->golongan_darah) }}" maxlength="3" placeholder="A/B/AB/O">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="nama_wali" class="form-label">Nama Wali</label>
                    <input type="text" class="form-control form-control-dark" id="nama_wali" name="nama_wali"
                           value="{{ old('nama_wali',$user->rekamMedis->nama_wali) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="hubungan_wali" class="form-label">Hubungan Wali</label>
                    <input type="text" class="form-control form-control-dark" id="hubungan_wali" name="hubungan_wali"
                           value="{{ old('hubungan_wali',$user->rekamMedis->hubungan_wali) }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="hp_wali" class="form-label">HP Wali</label>
                    <input type="text" class="form-control form-control-dark" id="hp_wali" name="hp_wali"
                           value="{{ old('hp_wali',$user->rekamMedis->hp_wali) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="jenis_pasien" class="form-label">Jenis Pasien</label>
                    <input type="text" class="form-control form-control-dark" id="jenis_pasien" name="jenis_pasien"
                           value="{{ old('jenis_pasien',$user->rekamMedis->jenis_pasien) }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="no_peserta" class="form-label">No Peserta</label>
                    <input type="text" class="form-control form-control-dark" id="no_peserta" name="no_peserta"
                           value="{{ old('no_peserta',$user->rekamMedis->no_peserta) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label for="nama_asuransi" class="form-label">Nama Asuransi</label>
                    <input type="text" class="form-control form-control-dark" id="nama_asuransi" name="nama_asuransi"
                           value="{{ old('nama_asuransi',$user->rekamMedis->nama_asuransi) }}">
                </div>
            </div>
        </div>
    @endif

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-gold px-4 fw-bold">Simpan Perubahan</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary-dark px-4 fw-bold">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection