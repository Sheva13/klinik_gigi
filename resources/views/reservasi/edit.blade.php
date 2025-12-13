@extends('layouts.adminlte')

@section('title', 'Ubah Jadwal Reservasi')

@section('content')

<style>
    :root {
        --gold-primary: #D4AF37;
        --gold-hover: #b89628;
        --bg-dark: #121212;
        --card-bg: #1A1A1A;
        --border-color: #333333;
        --text-muted: #a0a0a0;
        --input-bg: #121212;
    }

    h1, h2, h3, h4, h5, h6 { color: #fff !important; }
    .text-gold { color: var(--gold-primary) !important; }
    .text-muted { color: var(--text-muted) !important; }

    .edit-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
    }

    .form-label {
        color: #E0E0E0;
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .form-control-dark, .form-select-dark {
        background-color: var(--input-bg);
        border: 1px solid var(--border-color);
        color: #E0E0E0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
    }

    .form-control-dark:focus, .form-select-dark:focus {
        background-color: var(--input-bg);
        border-color: var(--gold-primary);
        color: #fff;
        box-shadow: none;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
        opacity: 0.7;
    }

    .btn-time-wrapper { position: relative; }

    .btn-check:checked + .btn-time {
        background-color: var(--gold-primary);
        color: #000;
        border-color: var(--gold-primary);
        font-weight: 700;
        box-shadow: 0 0 10px rgba(212, 175, 55, 0.3);
    }

    .btn-time {
        width: 100%;
        background-color: var(--input-bg);
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        padding: 10px 0;
        border-radius: 8px;
        transition: all 0.2s;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .btn-time:hover { border-color: var(--gold-primary); color: #FFF; }

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

    .btn-cancel {
        background-color: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 600;
        border-radius: 8px;
        padding: 0.75rem 2rem;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-block;
    }

    .btn-cancel:hover {
        background-color: rgba(255, 255, 255, 0.05);
        color: #FFF;
        border-color: #555;
    }
</style>

<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Ubah Jadwal <span class="text-gold">Reservasi</span></h1>
            <p class="text-muted mb-0">Perbarui detail reservasi pasien.</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="text-end d-none d-md-block">
                {{-- Data Admin dinamis --}}
                <div class="fw-bold text-white">{{ Auth::guard('admin')->user()->nama ?? 'Admin' }}</div>
                <small class="text-muted">Administrator</small>
            </div>
            <img src="{{ asset('assets/images/profile/wais.jpg') }}"
                 alt="Profile"
                 class="rounded-circle border border-secondary"
                 style="width: 45px; height: 45px; object-fit: cover;">
        </div>
    </div>

    <div class="edit-card">
        <div class="mb-4 border-bottom border-secondary pb-3">
            <h4 class="fw-bold mb-1">Form Perubahan Data</h4>
            <p class="text-muted mb-0">Pasien:
                <span class="text-gold fw-bold">{{ $reservasi->rekamMedis->nama ?? 'Nama Pasien' }}</span>
                ({{ $reservasi->rekamMedis->rekam_medis ?? 'No RM' }})
            </p>
        </div>

        {{-- Form Update ke Controller --}}
        <form action="{{ route('reservasi.admin.update', $reservasi->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="tanggal_pesan" class="form-label">Tanggal Reservasi Baru</label>
                <div class="input-group">
                    <span class="input-group-text input-group-text-dark"
                          style="background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-muted);">
                        <span class="material-symbols-outlined fs-5">calendar_month</span>
                    </span>
                    <input type="date" class="form-control form-control-dark" style="border-left:none;"
                           id="tanggal_pesan" name="tanggal_pesan"
                           value="{{ old('tanggal_pesan', $reservasi->tanggal_pesan) }}" required>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label for="poli_id" class="form-label">Poli Baru</label>
                    <select class="form-select form-select-dark" id="poli_id" name="poli_id">
                        <option value="">Pilih Poli</option>

                        @if (isset($polis))
                            @foreach($polis as $poli)
                                @php
                                    $selected = optional($reservasi->dokter->masterPoli)->kode_poli == $poli->kode_poli ? 'selected' : '';
                                @endphp
                                <option value="{{ $poli->kode_poli }}" {{ $selected }}>
                                    {{ $poli->nama_poli }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="dokter_id" class="form-label">Dokter Baru</label>
                    <select class="form-select form-select-dark" id="dokter_id" name="dokter_id">
                        <option value="">Pilih Dokter</option>

                        @if (isset($dokters))
                            @foreach($dokters as $dokter)
                                @php
                                    $selected = $reservasi->dokter_id == $dokter->kode_dokter ? 'selected' : '';
                                @endphp
                                <option value="{{ $dokter->kode_dokter }}" {{ $selected }}>
                                    {{ $dokter->nama }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            @php
                // List Jam Praktek
                $jamPraktek = ['09:00', '09:30', '11:00', '13:00', '13:30', '14:00', '15:00'];
                
                // 🔥 LOGIC FIX: Ambil dari $reservasi->jam_mulai (Jam Janji Pasien)
                // Bukan dari $reservasi->jadwal (Jam Buka Klinik)
                $jamSaatIni = $reservasi->jam_mulai 
                    ? \Carbon\Carbon::parse($reservasi->jam_mulai)->format('H:i') 
                    : null;
            @endphp

            <div class="mb-4">
                <label class="form-label d-block mb-3">Waktu Tersedia</label>

                <div class="row row-cols-3 row-cols-sm-4 row-cols-md-6 g-3">
                    @foreach($jamPraktek as $jam)
                    <div class="col btn-time-wrapper">
                        <input type="radio" class="btn-check" name="jam_praktek"
                               id="jam_{{ $loop->index }}" value="{{ $jam }}"
                               {{ $jam == $jamSaatIni ? 'checked' : '' }}>
                        <label class="btn btn-time" for="jam_{{ $loop->index }}">
                            {{ $jam }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 🔥 TAMBAHAN: Dropdown Status Pembayaran --}}
            <div class="mb-4">
                <label for="status_pembayaran" class="form-label">Status Pembayaran</label>
                <select class="form-select form-select-dark" id="status_pembayaran" name="status_pembayaran">
                    <option value="menunggu_pembayaran" {{ $reservasi->status_pembayaran == 'menunggu_pembayaran' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="menunggu_verifikasi" {{ $reservasi->status_pembayaran == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="terverifikasi" {{ $reservasi->status_pembayaran == 'terverifikasi' ? 'selected' : '' }}>Lunas / Terverifikasi</option>
                    <option value="gagal" {{ $reservasi->status_pembayaran == 'gagal' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>

            <div class="mb-5">
                <label for="alasan" class="form-label">Alasan Perubahan</label>
                <textarea class="form-control form-control-dark" id="alasan" name="alasan" rows="4"
                          placeholder="Tuliskan alasan kenapa jadwal diubah...">{{ old('alasan') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-2 border-top border-secondary">
                {{-- Route Batal kembali ke Index (Aman) --}}
                <a href="{{ route('reservasi.admin.index') }}" class="btn btn-cancel">Batal</a>
                <button type="submit" class="btn btn-gold">
                    <span class="material-symbols-outlined" style="font-size: 1.2rem;">save</span>
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

</div>

@endsection