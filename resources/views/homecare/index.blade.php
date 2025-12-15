@extends('layouts.adminlte')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: { "primary": "#f5c542", "background-dark": "#121212" },
                fontFamily: { "display": ["Manrope", "sans-serif"] }
            },
        },
    }
</script>

<div class="content-wrapper bg-[#121212] text-white" style="min-height: 100vh; margin-left: 0; background-color: #121212;">
    <main class="flex-1 p-8">
        <div class="w-full max-w-7xl mx-auto">
            <div class="flex flex-wrap justify-between items-center gap-4 mb-8">
                <div>
                    <h1 class="text-white text-3xl font-bold leading-tight">Riwayat Home Care</h1>
                    <p class="text-gray-400 mt-1">Monitoring Status Kunjungan Pasien</p>
                </div>
            </div>

            <form action="{{ route('homecare.index') }}" method="GET" class="bg-[#1A1A1A] p-4 rounded-xl border border-gray-800 mb-6 flex flex-col md:flex-row gap-4">
                <div class="flex-grow">
                    <label class="text-xs text-gray-400 mb-1 block">Cari Nama Pasien</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input name="search" value="{{ request('search') }}" class="w-full h-10 rounded-lg bg-[#2C2C2C] border-none text-white placeholder:text-gray-500 pl-10 pr-4 focus:ring-2 focus:ring-[#f5c542]" placeholder="Nama pasien..." type="text"/>
                    </div>
                </div>

                <div class="flex-none w-full md:w-48">
                    <label class="text-xs text-gray-400 mb-1 block">Status</label>
                    <select name="status" class="w-full h-10 rounded-lg bg-[#2C2C2C] border-none text-white px-4 focus:ring-2 focus:ring-[#f5c542]">
                        <option value="">Semua Status</option>
                        <option value="Ditugaskan" {{ request('status') == 'Ditugaskan' ? 'selected' : '' }}>Ditugaskan</option>
                        <option value="OTW" {{ request('status') == 'OTW' ? 'selected' : '' }}>OTW</option>
                        <option value="Pemeriksaan" {{ request('status') == 'Pemeriksaan' ? 'selected' : '' }}>Pemeriksaan</option>
                        <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <div class="flex-none">
                    <label class="text-xs text-gray-400 mb-1 block">Dari Tanggal</label>
                    <input name="start_date" value="{{ request('start_date') }}" class="h-10 rounded-lg bg-[#2C2C2C] border-none text-white placeholder:text-gray-500 px-4 focus:ring-2 focus:ring-[#f5c542]" type="date"/>
                </div>
                <div class="flex-none">
                    <label class="text-xs text-gray-400 mb-1 block">Sampai Tanggal</label>
                    <input name="end_date" value="{{ request('end_date') }}" class="h-10 rounded-lg bg-[#2C2C2C] border-none text-white placeholder:text-gray-500 px-4 focus:ring-2 focus:ring-[#f5c542]" type="date"/>
                </div>

                <div class="flex-none flex items-end">
                    <button type="submit" class="h-10 px-6 rounded-lg bg-[#f5c542] text-black font-bold hover:bg-[#e4a93c] transition-colors w-full md:w-auto">
                        Filter
                    </button>
                </div>
            </form>

            <div class="space-y-4">
                @forelse($riwayat as $data)
                <div class="bg-[#1A1A1A] rounded-xl overflow-hidden border border-gray-800 transition hover:border-[#f5c542]/50">
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 items-center w-full mb-6">
                            <div>
                                <p class="text-xs text-gray-400">Pasien</p>
                                <p class="text-white font-semibold text-lg">{{ $data->nama_pasien }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">No. Reservasi</p>
                                <p class="text-gray-300 font-mono">{{ $data->no_reservasi }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Dokter</p>
                                <p class="text-gray-300">{{ $data->nama_dokter ?? 'Belum ada' }}</p>
                            </div>
                            <div class="flex justify-end">
                                <a href="{{ route('homecare.show', $data->id) }}" class="flex items-center justify-center gap-x-2 rounded-lg bg-white/5 border border-white/10 h-10 px-4 text-white font-medium text-sm hover:bg-[#f5c542] hover:text-black hover:border-[#f5c542] transition-all">
                                    <i class="fas fa-edit"></i>
                                    <span>Detail & Ubah</span>
                                </a>
                            </div>
                        </div>

                        <div class="relative pt-2">
                            @php
                                $statuses = ['Ditugaskan', 'OTW', 'Pemeriksaan', 'Selesai'];
                                $currentStatusIndex = array_search($data->status, $statuses);
                                // Jika status tidak ditemukan (misal: Menunggu), index jadi -1 (abu-abu semua)
                                if ($currentStatusIndex === false) $currentStatusIndex = -1; 
                            @endphp
                            
                            <div class="flex items-center justify-between relative z-10">
                                @foreach($statuses as $index => $status)
                                    <div class="flex flex-col items-center flex-1">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-colors duration-300
                                            {{ $index <= $currentStatusIndex ? 'bg-[#f5c542] border-[#f5c542] text-black' : 'bg-[#2C2C2C] border-gray-600 text-gray-500' }}">
                                            @if($status == 'Ditugaskan') <i class="fas fa-user-md text-xs"></i>
                                            @elseif($status == 'OTW') <i class="fas fa-car text-xs"></i>
                                            @elseif($status == 'Pemeriksaan') <i class="fas fa-stethoscope text-xs"></i>
                                            @elseif($status == 'Selesai') <i class="fas fa-check text-xs"></i>
                                            @endif
                                        </div>
                                        <p class="text-[10px] mt-1 font-medium {{ $index <= $currentStatusIndex ? 'text-[#f5c542]' : 'text-gray-500' }}">{{ $status }}</p>
                                    </div>
                                    
                                    @if(!$loop->last)
                                        <div class="absolute top-4 h-[2px] -z-10 bg-gray-700" style="left: {{ 12.5 + ($index * 25) }}%; width: 25%;">
                                            <div class="h-full transition-all duration-500 {{ $index < $currentStatusIndex ? 'bg-[#f5c542]' : '' }}"></div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-gray-500 py-10 bg-[#1A1A1A] rounded-xl border border-gray-800">
                    <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                    <p>Tidak ada data ditemukan sesuai filter.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $riwayat->links() }}
            </div>
        </div>
    </main>
</div>
@endsection