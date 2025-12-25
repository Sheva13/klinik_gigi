@extends('layouts.adminlte')

@section('title', 'Ubah Jadwal Reservasi')

@section('content')

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
                           {{-- KOREKSI: Pastikan format value tanggal adalah YYYY-MM-DD --}}
                           value="{{ old('tanggal_pesan', \Carbon\Carbon::parse($reservasi->tanggal_pesan)->format('Y-m-d')) }}" required>
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
                // List Jam Praktek (Dummy List - aslinya harusnya dari API/DB berdasarkan Dokter/Tanggal)
                $jamPraktek = ['09:00', '09:30', '11:00', '13:00', '13:30', '14:00', '15:00'];

                // 🔥 LOGIC FIX: Ambil jam mulai reservasi yang dipilih
                $jamSaatIni = $reservasi->jam_mulai
                    ? \Carbon\Carbon::parse($reservasi->jam_mulai)->format('H:i')
                    : null;
            @endphp

            <div class="mb-4">
                <label class="form-label d-block mb-3">Waktu Tersedia</label>

                <div class="row row-cols-3 row-cols-sm-4 row-cols-md-6 g-3">
                    @foreach($jamPraktek as $jam)
                    <div class="col btn-time-wrapper">
                        {{-- KOREKSI: Name input jam praktek harus 'jam_praktek' sesuai controller --}}
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
                    <option value="menunggu_pembayaran" {{ $reservasi->status_pembayaran == 'menunggu_pembayaran' ? 'selected' : '' }}>Belum Bayar (Online)</option>
                    <option value="menunggu_verifikasi" {{ $reservasi->status_pembayaran == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi (Manual)</option>
                    {{-- KOREKSI LIXA: Tambahkan status lunas dari Webhook --}}
                    <option value="lunas" {{ $reservasi->status_pembayaran == 'lunas' ? 'selected' : '' }}>Lunas (Online)</option>
                    <option value="terverifikasi" {{ $reservasi->status_pembayaran == 'terverifikasi' ? 'selected' : '' }}>Lunas / Terverifikasi (Admin)</option>
                    <option value="gagal" {{ $reservasi->status_pembayaran == 'gagal' ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>

            <div class="mb-5">
                <label for="alasan" class="form-label">Alasan Perubahan</label>
                {{-- KOREKSI LIXA: Ambil nilai keluhan yang sudah ada --}}
                <textarea class="form-control form-control-dark" id="alasan" name="alasan" rows="4"
                           placeholder="Tuliskan alasan kenapa jadwal diubah...">{{ old('alasan', $reservasi->keluhan) }}</textarea>
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