@extends('layouts.adminlte')

@section('title', 'Jadwal Praktik')

@section('styles')
<style>
    /* Styling Khusus Halaman Jadwal */
    .bg-dark-card {
        background-color: #1E1E1E;
        border: 1px solid #333;
    }
    .dokter-card {
        transition: all 0.2s;
        cursor: pointer;
        border: 1px solid transparent;
    }
    .dokter-card:hover {
        background-color: rgba(245, 197, 66, 0.05);
        border-color: #f5c542;
    }
    .dokter-card.active {
        background-color: rgba(245, 197, 66, 0.1);
        border-color: #f5c542;
    }
    .btn-gold {
        background-color: #f5c542;
        color: #121212;
        font-weight: 700;
        border: none;
    }
    .btn-gold:hover {
        background-color: #e0b134;
        color: #000;
    }
    .text-gold {
        color: #f5c542;
    }
    /* Form inputs dark mode */
    .form-control-dark {
        background-color: #2C2C2C;
        border: 1px solid #444;
        color: #fff;
    }
    .form-control-dark:focus {
        background-color: #2C2C2C;
        color: #fff;
        border-color: #f5c542;
        box-shadow: 0 0 0 0.25rem rgba(245, 197, 66, 0.25);
    }
    .form-select-dark {
        background-color: #2C2C2C;
        border: 1px solid #444;
        color: #fff;
    }

    /* --- GAYA UNTUK AVATAR INISIAL --- */
    .avatar-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #121212;
        background-color: #f5c542; /* Warna Emas */
        border-radius: 50%;
        text-transform: uppercase;
    }
    /* Ukuran kecil untuk list di kiri */
    .avatar-sm {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
    /* Ukuran besar untuk header di kanan */
    .avatar-lg {
        width: 70px;
        height: 70px;
        font-size: 28px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white">Manajemen Jadwal Praktik</h2>
            <p class="text-secondary mb-0">Atur jadwal hari dan jam praktik untuk setiap dokter.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/" class="text-secondary text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Jadwal Praktik</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card bg-dark-card text-white h-100 shadow-sm">
                <div class="card-body">
                    <div class="input-group mb-4">
                        <span class="input-group-text bg-dark border-secondary text-secondary">
                            <span class="material-symbols-outlined fs-5">search</span>
                        </span>
                        {{-- PERUBAHAN 1: Tambahkan ID cariDokterInput --}}
                        <input type="text" id="cariDokterInput" class="form-control form-control-dark" placeholder="Cari dokter...">
                    </div>

                    <div class="d-flex flex-column gap-3" style="max-height: 600px; overflow-y: auto;">
                        @foreach($dokters as $dokter)
                        {{-- PERUBAHAN 2: Tambahkan class dokter-item --}}
                        <a href="{{ route('jadwal.index', ['dokter_id' => $dokter->kode_dokter]) }}" class="text-decoration-none dokter-item">
                            <div class="card bg-transparent dokter-card p-3 rounded-3 {{ ($selectedDokter && $selectedDokter->kode_dokter == $dokter->kode_dokter) ? 'active' : '' }}">
                                <div class="d-flex align-items-center gap-3">
                                    
                                    @if($dokter->file_foto && file_exists(public_path('storage/'.$dokter->file_foto)))
                                        <img src="{{ asset('storage/'.$dokter->file_foto) }}" 
                                             class="rounded-circle object-fit-cover" 
                                             style="width: 50px; height: 50px;" 
                                             alt="{{ $dokter->nama }}">
                                    @else
                                        <div class="avatar-initial avatar-sm">
                                            {{ $dokter->inisial ? $dokter->inisial : substr($dokter->nama, 0, 1) }}
                                        </div>
                                    @endif
                                    
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 text-white fw-bold">{{ $dokter->nama }}</h6>
                                        <small class="text-secondary">
                                            {{ $dokter->spesialis ? $dokter->spesialis->nama_spesialis : 'Dokter Umum' }}
                                        </small>
                                    </div>

                                    @if($selectedDokter && $selectedDokter->kode_dokter == $dokter->kode_dokter)
                                    <span class="material-symbols-outlined text-gold">chevron_right</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card bg-dark-card text-white shadow-sm h-100">
                <div class="card-body p-4">
                    @if($selectedDokter)
                        <div class="d-flex justify-content-between align-items-start border-bottom border-secondary pb-4 mb-4">
                            <div class="d-flex align-items-center gap-4">
                                
                                @if($selectedDokter->file_foto && file_exists(public_path('storage/'.$selectedDokter->file_foto)))
                                    <img src="{{ asset('storage/'.$selectedDokter->file_foto) }}" 
                                         class="rounded-circle object-fit-cover border border-secondary" 
                                         style="width: 70px; height: 70px;">
                                @else
                                    <div class="avatar-initial avatar-lg border border-secondary">
                                        {{ $selectedDokter->inisial ? $selectedDokter->inisial : substr($selectedDokter->nama, 0, 1) }}
                                    </div>
                                @endif

                                <div>
                                    <small class="text-gold text-uppercase fw-bold ls-1">Jadwal Praktik</small>
                                    <h3 class="fw-bold mb-0 text-white">{{ $selectedDokter->nama }}</h3>
                                    <span class="badge bg-secondary mt-2">{{ $selectedDokter->kode_dokter }}</span>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-gold d-flex align-items-center gap-2 px-3" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                                <span class="material-symbols-outlined">add_circle</span>
                                <span>Tambah Jadwal</span>
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle" style="background-color: transparent;">
                                <thead>
                                    <tr class="text-secondary text-uppercase fs-7 border-bottom border-secondary">
                                        <th style="background: transparent;">Hari</th>
                                        <th style="background: transparent;">Jam Praktik</th>
                                        <th style="background: transparent;">Poli</th>
                                        <th style="background: transparent;">Quota</th>
                                        <th style="background: transparent;" class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jadwals as $jadwal)
                                    @php
                                        $namaHari = match($jadwal->hari) {
                                            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu', default => 'Unknown'
                                        };
                                    @endphp
                                    <tr>
                                        <td class="bg-transparent fw-bold text-white">{{ $namaHari }}</td>
                                        <td class="bg-transparent">
                                            <span class="badge bg-dark border border-secondary px-3 py-2">
                                                {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                            </span>
                                        </td>
                                        <td class="bg-transparent text-secondary">
                                            {{ $jadwal->poli ? $jadwal->poli->nama_poli : '-' }}
                                        </td>
                                        <td class="bg-transparent text-secondary">
                                            {{ $jadwal->quota ?? 'Unlimited' }} Pasien
                                        </td>
                                        <td class="bg-transparent text-end">
                                            <button type="button" 
                                                    onclick="editJadwal({{ $jadwal->id }})" 
                                                    class="btn btn-link text-warning p-0 me-2" 
                                                    title="Edit Jadwal">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                            <form action="{{ route('jadwal.destroy', $jadwal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jadwal ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0">
                                                    <span class="material-symbols-outlined">delete</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 bg-transparent text-secondary">
                                            <span class="material-symbols-outlined fs-1 d-block mb-2">calendar_today</span>
                                            Belum ada jadwal praktik untuk dokter ini.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 text-secondary">
                            <span class="material-symbols-outlined fs-1 mb-2">person_search</span>
                            <p>Silakan pilih dokter di panel sebelah kiri untuk melihat jadwal.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($selectedDokter)
<div class="modal fade" id="modalTambahJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark-card text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title">Tambah Jadwal Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('jadwal.store') }}" method="POST">
                @csrf
                <input type="hidden" name="kode_dokter" value="{{ $selectedDokter->kode_dokter }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Poli / Ruangan</label>
                        <select name="kode_poli" class="form-select form-select-dark" required>
                            @foreach($polis as $poli)
                                <option value="{{ $poli->kode_poli }}">{{ $poli->nama_poli }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary">Hari</label>
                        <select name="hari" class="form-select form-select-dark" required>
                            <option value="1">Senin</option>
                            <option value="2">Selasa</option>
                            <option value="3">Rabu</option>
                            <option value="4">Kamis</option>
                            <option value="5">Jumat</option>
                            <option value="6">Sabtu</option>
                            <option value="7">Minggu</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-secondary">Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-secondary">Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="form-control form-control-dark" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary">Quota (Opsional)</label>
                        <input type="number" name="quota" class="form-control form-control-dark" placeholder="Maksimal pasien (kosongkan jika unlimited)">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary">Keterangan (Opsional)</label>
                        <input type="text" name="keterangan" class="form-control form-control-dark" placeholder="Contoh: Istirahat 12:00-13:00">
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gold">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditJadwal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark-card text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title">Edit Jadwal Praktik</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditJadwal" method="POST">
                @csrf
                @method('PUT')
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Poli / Ruangan</label>
                        <select name="kode_poli" id="edit_kode_poli" class="form-select form-select-dark" required>
                            @foreach($polis as $poli)
                                <option value="{{ $poli->kode_poli }}">{{ $poli->nama_poli }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary">Hari</label>
                        <select name="hari" id="edit_hari" class="form-select form-select-dark" required>
                            <option value="1">Senin</option>
                            <option value="2">Selasa</option>
                            <option value="3">Rabu</option>
                            <option value="4">Kamis</option>
                            <option value="5">Jumat</option>
                            <option value="6">Sabtu</option>
                            <option value="7">Minggu</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label text-secondary">Jam Mulai</label>
                            <input type="time" name="jam_mulai" id="edit_jam_mulai" class="form-control form-control-dark" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label text-secondary">Jam Selesai</label>
                            <input type="time" name="jam_selesai" id="edit_jam_selesai" class="form-control form-control-dark" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary">Quota (Opsional)</label>
                        <input type="number" name="quota" id="edit_quota" class="form-control form-control-dark" placeholder="Unlimited">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary">Keterangan (Opsional)</label>
                        <input type="text" name="keterangan" id="edit_keterangan" class="form-control form-control-dark">
                    </div>
                </div>
                
                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- PERUBAHAN 3: Script dipindah ke sini (di luar if selectedDokter) agar fungsi search selalu ada --}}
<script>
    // FUNGSI PENCARIAN (Live Search)
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('cariDokterInput');
        if(searchInput){
            searchInput.addEventListener('keyup', function() {
                let filter = this.value.toLowerCase();
                let items = document.querySelectorAll('.dokter-item');

                items.forEach(function(item) {
                    let nameElement = item.querySelector('h6');
                    if(nameElement) {
                        let name = nameElement.innerText.toLowerCase();
                        if (name.indexOf(filter) > -1) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });
            });
        }
    });

    // FUNGSI EDIT JADWAL
    function editJadwal(id) {
        fetch(`/jadwal/${id}/edit`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal mengambil data, pastikan route edit sudah dibuat');
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_kode_poli').value = data.kode_poli;
                document.getElementById('edit_hari').value = data.hari;
                
                let jamMulai = data.jam_mulai ? data.jam_mulai.substring(0, 5) : '';
                let jamSelesai = data.jam_selesai ? data.jam_selesai.substring(0, 5) : '';

                document.getElementById('edit_jam_mulai').value = jamMulai;
                document.getElementById('edit_jam_selesai').value = jamSelesai;
                
                document.getElementById('edit_quota').value = data.quota;
                document.getElementById('edit_keterangan').value = data.keterangan;

                let form = document.getElementById('formEditJadwal');
                form.action = `/jadwal/${id}`;

                let myModal = new bootstrap.Modal(document.getElementById('modalEditJadwal'));
                myModal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat data jadwal.');
            });
    }
</script>

@endsection