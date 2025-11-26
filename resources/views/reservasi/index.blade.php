@extends('layouts.adminlte')

@section('title', 'Data Reservasi')

@section('styles')
<style>
    .table-dark-custom {
        background-color: #1A1A1A;
        color: #E0E0E0;
    }
    .table-dark-custom th, .table-dark-custom td {
        border-color: #333333;
        vertical-align: middle;
    }
    .table-dark-custom tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    .btn-action {
        padding: 4px 8px;
        font-size: 0.8rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold mb-0">Kelola Reservasi</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show bg-success text-white border-0">
            {{ session('success') }}
            <button class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="background-color: #1A1A1A; border: 1px solid #333;">
        <div class="card-body p-0">
            <div class="table-responsive">

                <table class="table table-dark-custom mb-0">
                    <thead style="background-color: #252525;">
                        <tr>
                            <th class="p-3">No Pemeriksaan</th>
                            <th class="p-3">Pasien</th>
                            <th class="p-3">Dokter & Poli</th>
                            <th class="p-3">Jadwal</th>
                            <th class="p-3">Status Pembayaran</th>
                            <th class="p-3">Status Reservasi</th>
                            <th class="p-3 text-end">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($data as $item)
                        <tr>

                            {{-- No Pemeriksaan --}}
                            <td class="p-3">
                                <span class="fw-bold text-warning">{{ $item->no_pemeriksaan ?? '-' }}</span><br>
                                <small class="text-secondary">{{ $item->created_at->format('d M Y H:i') }}</small>
                            </td>

                            {{-- Pasien --}}
                            <td class="p-3">
                                {{ $item->rekamMedis->nama ?? '-' }}<br>
                                <small class="text-secondary">
                                    RM: {{ $item->rekamMedis->rekam_medis ?? '-' }}
                                </small>
                            </td>

                            {{-- Dokter & Poli --}}
                            <td class="p-3">
                                <div class="fw-bold">{{ $item->dokter->nama ?? '-' }}</div>
                                <small class="text-secondary">
                                    {{ $item->poli->nama_poli ?? '-' }}
                                </small>
                            </td>

                            {{-- Jadwal --}}
                            <td class="p-3">
                                {{ \Carbon\Carbon::parse($item->tanggal_pesan)->format('d M Y') }}<br>
                                <span class="badge bg-secondary">
                                    {{ $item->jadwal->jam_mulai }} - {{ $item->jadwal->jam_selesai }}
                                </span>
                            </td>

                            {{-- Status Pembayaran --}}
                            <td class="p-3">
                                @if($item->status_pembayaran == 'waiting')
                                    <span class="badge bg-warning text-dark">Menunggu</span>

                                @elseif($item->status_pembayaran == 'verified')
                                    <span class="badge bg-success">Terverifikasi</span>

                                @elseif($item->status_pembayaran == 'cancelled')
                                    <span class="badge bg-danger">Dibatalkan</span>

                                @else
                                    <span class="badge bg-secondary">{{ $item->status_pembayaran }}</span>
                                @endif

                                <div class="small mt-1 text-secondary">
                                    {{ $item->metode_pembayaran ?? '-' }}
                                </div>
                            </td>

                            {{-- Status reservasi --}}
                            <td class="p-3">
                                @if($item->status_reservasi == 'waiting')
                                    <span class="badge border border-warning text-warning">Menunggu</span>

                                @elseif($item->status_reservasi == 'process')
                                    <span class="badge border border-info text-info">Proses</span>

                                @elseif($item->status_reservasi == 'completed')
                                    <span class="badge border border-success text-success">Selesai</span>

                                @elseif($item->status_reservasi == 'cancelled')
                                    <span class="badge border border-danger text-danger">Dibatalkan</span>

                                @else
                                    <span class="badge border border-secondary text-secondary">-</span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="p-3 text-end">
                                <a href="{{ route('reservasi.admin.show', $item->id) }}" 
                                   class="btn btn-primary btn-sm btn-action">
                                    Detail
                                </a>
                            </td>

                        </tr>

                        @empty
                        <tr>
                            <td colspan="7" class="text-center p-5 text-secondary">
                                Tidak ada data reservasi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

        <div class="p-3">
            {{ $data->links('pagination::bootstrap-5') }}
        </div>

    </div>
</div>
@endsection
