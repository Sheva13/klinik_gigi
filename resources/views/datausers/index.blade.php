@extends('layouts.adminlte')

@section('title', 'Data User')

@section('content')

<div class="container-fluid px-0">

    <div class="d-flex align-items-center gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="font-size: 1.8rem;">Data <span class="text-gold">User</span></h1>
            <p class="text-muted mb-0">Manajemen data pengguna & pasien</p>
        </div>
    </div>

    <div class="card card-dark-premium border-0 mb-4">
        <div class="card-header border-bottom border-secondary py-3">
            <div class="d-flex align-items-center">
                <span class="material-symbols-outlined text-gold me-2">filter_alt</span>
                <h5 class="mb-0 text-white fw-bold">Filter Data</h5>
            </div>
        </div>
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="small text-muted mb-2">Cari nama / NIK</label>
                    <input type="text" name="search" class="form-control form-control-dark"
                           placeholder="Masukkan kata kunci..." value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <label class="small text-muted mb-2">Tipe Pasien</label>
                    <select name="tipe_pasien" class="form-select form-select-dark">
                        <option value="">Semua</option>
                        <option value="baru" {{ request('tipe_pasien')=='baru'?'selected':'' }}>Pasien Baru</option>
                        <option value="lama" {{ request('tipe_pasien')=='lama'?'selected':'' }}>Pasien Lama</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="small text-muted mb-2 d-none d-md-block">&nbsp;</label>
                    <button class="btn btn-gold w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-dark-premium border-0">
        <div class="card-header border-bottom border-secondary py-3">
            <div class="d-flex align-items-center">
                <span class="material-symbols-outlined text-info me-2">list</span>
                <h5 class="mb-0 text-white fw-bold">Daftar User</h5>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark-custom mb-0">
                    <thead class="bg-dark-premium">
                    <tr>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Email</th>
                        <th>Tipe</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                                        <span class="material-symbols-outlined">person</span>
                                    </div>
                                    <div>
                                        <div class="text-white fw-bold">{{ $user->nama_pengguna }}</div>
                                        <div class="text-muted small">{{ $user->user_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-white">{{ $user->nik ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="text-white">{{ $user->email ?? '-' }}</div>
                            </td>
                            <td>
                                @if($user->rekamMedis)
                                    <span class="badge bg-warning-soft text-warning">{{ $user->rekamMedis->tipe_pasien ?? 'Tidak Diketahui' }}</span>
                                @else
                                    <span class="badge bg-secondary">Pengguna</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.users.show', $user->user_id) }}" class="btn btn-sm btn-outline-light border-0" title="Lihat Detail">
                                        <span class="material-symbols-outlined text-gold">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->user_id) }}" class="btn btn-sm btn-outline-light border-0" title="Edit">
                                        <span class="material-symbols-outlined text-gold">edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <span class="material-symbols-outlined fs-1 mb-2 opacity-50">folder_open</span>
                                <p class="mb-0">Belum ada data user.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-end mt-4">
        <nav>
            @if($users->hasPages())
                <ul class="pagination pagination-sm">
                    @if ($users->onFirstPage())
                        <li class="page-item disabled"><span class="page-link bg-transparent border-secondary text-muted">&laquo;</span></li>
                    @else
                        <li class="page-item">
                            <a class="page-link bg-transparent border-secondary text-muted"
                               href="{{ $users->appends(request()->all())->previousPageUrl() }}">&laquo;</a>
                        </li>
                    @endif

                    <li class="page-item active"><span class="page-link bg-gold border-gold text-dark fw-bold">{{ $users->currentPage() }}</span></li>

                    @if ($users->hasMorePages())
                        <li class="page-item">
                            <a class="page-link bg-transparent border-secondary text-muted"
                               href="{{ $users->appends(request()->all())->nextPageUrl() }}">&raquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled"><span class="page-link bg-transparent border-secondary text-muted">&raquo;</span></li>
                    @endif
                </ul>
            @endif
        </nav>
    </div>

</div>
@endsection
