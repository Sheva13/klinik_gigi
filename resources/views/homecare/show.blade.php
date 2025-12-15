@extends('layouts.adminlte')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: { "primary": "#f5c542" },
                fontFamily: { "display": ["Manrope", "sans-serif"] }
            },
        },
    }
</script>
<style>
    .timeline-connector { flex-grow: 1; height: 2px; background-color: #374151; }
    .timeline-connector-active { background-color: #f5c542; }
</style>

<div class="content-wrapper bg-[#121212] text-white" style="min-height: 100vh; margin-left: 0; background-color: #121212;">
    <main class="flex-1 p-8">
        <div class="w-full max-w-7xl mx-auto">
            <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
                <div>
                    <h1 class="text-white text-3xl font-bold leading-tight">Detail Kunjungan</h1>
                    <p class="text-gray-400 mt-1">Kelola data dan status pemeriksaan.</p>
                </div>
            </div>

            <div class="bg-[#1A1A1A] rounded-xl border border-gray-800 p-6 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-xs text-gray-400">Nama Pasien</p>
                        <p class="text-white font-semibold mt-1">{{ $item->nama_pasien }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">No Reservasi</p>
                        <p class="text-white font-semibold mt-1">{{ $item->no_reservasi }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Dokter</p>
                        <p class="text-white font-semibold mt-1">{{ $item->nama_dokter ?? 'Belum Ditentukan' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Tanggal</p>
                        <p class="text-white font-semibold mt-1">{{ \Carbon\Carbon::parse($item->tgl_reservasi)->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-[#1A1A1A] rounded-xl border border-gray-800 p-6">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-bold text-white">Update Status Pemeriksaan</h3>
                    <div class="px-3 py-1 rounded-full text-sm font-medium bg-[#f5c542]/20 text-[#f5c542]">
                        Status Saat Ini: {{ ucfirst($item->status) }}
                    </div>
                </div>

                <form action="{{ route('homecare.update-status', $item->id) }}" method="POST" class="w-full">
                    @csrf
                    @method('PUT')
                    
                    <div class="w-full flex items-start justify-between relative">
                        
                        <label class="flex-1 flex flex-col items-center group cursor-pointer relative z-10">
                            <input type="radio" name="status" value="Ditugaskan" class="peer hidden" 
                                {{ $item->status == 'Ditugaskan' ? 'checked' : '' }}>
                            
                            <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300
                                {{ $item->status == 'Ditugaskan' || $item->status == 'OTW' || $item->status == 'Pemeriksaan' || $item->status == 'Selesai' ? 'bg-[#f5c542] text-black' : 'bg-gray-700 text-gray-400' }}
                                peer-checked:ring-4 peer-checked:ring-[#f5c542]/30 peer-checked:bg-[#f5c542] peer-checked:text-black peer-checked:scale-110">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <p class="text-xs mt-2 font-semibold text-gray-400 peer-checked:text-[#f5c542] {{ $item->status == 'Ditugaskan' ? 'text-[#f5c542]' : '' }}">Dokter Ditugaskan</p>
                        </label>

                        <div class="timeline-connector mt-6 {{ in_array($item->status, ['OTW', 'Pemeriksaan', 'Selesai']) ? 'timeline-connector-active' : '' }}"></div>

                        <label class="flex-1 flex flex-col items-center group cursor-pointer relative z-10">
                            <input type="radio" name="status" value="OTW" class="peer hidden" 
                                {{ $item->status == 'OTW' ? 'checked' : '' }}>
                            
                            <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300
                                {{ $item->status == 'OTW' || $item->status == 'Pemeriksaan' || $item->status == 'Selesai' ? 'bg-[#f5c542] text-black' : 'bg-gray-700 text-gray-400' }}
                                peer-checked:ring-4 peer-checked:ring-[#f5c542]/30 peer-checked:bg-[#f5c542] peer-checked:text-black peer-checked:scale-110">
                                <i class="fas fa-car"></i>
                            </div>
                            <p class="text-xs mt-2 font-semibold text-gray-400 peer-checked:text-[#f5c542] {{ $item->status == 'OTW' ? 'text-[#f5c542]' : '' }}">Dokter OTW</p>
                        </label>

                        <div class="timeline-connector mt-6 {{ in_array($item->status, ['Pemeriksaan', 'Selesai']) ? 'timeline-connector-active' : '' }}"></div>

                        <label class="flex-1 flex flex-col items-center group cursor-pointer relative z-10">
                            <input type="radio" name="status" value="Pemeriksaan" class="peer hidden" 
                                {{ $item->status == 'Pemeriksaan' ? 'checked' : '' }}>
                            
                            <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300
                                {{ $item->status == 'Pemeriksaan' || $item->status == 'Selesai' ? 'bg-[#f5c542] text-black' : 'bg-gray-700 text-gray-400' }}
                                peer-checked:ring-4 peer-checked:ring-[#f5c542]/30 peer-checked:bg-[#f5c542] peer-checked:text-black peer-checked:scale-110">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <p class="text-xs mt-2 font-semibold text-gray-400 peer-checked:text-[#f5c542] {{ $item->status == 'Pemeriksaan' ? 'text-[#f5c542]' : '' }}">Sedang Diperiksa</p>
                        </label>

                        <div class="timeline-connector mt-6 {{ $item->status == 'Selesai' ? 'timeline-connector-active' : '' }}"></div>

                        <label class="flex-1 flex flex-col items-center group cursor-pointer relative z-10">
                            <input type="radio" name="status" value="Selesai" class="peer hidden" 
                                {{ $item->status == 'Selesai' ? 'checked' : '' }}>
                            
                            <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300
                                {{ $item->status == 'Selesai' ? 'bg-[#f5c542] text-black' : 'bg-gray-700 text-gray-400' }}
                                peer-checked:ring-4 peer-checked:ring-[#f5c542]/30 peer-checked:bg-[#f5c542] peer-checked:text-black peer-checked:scale-110">
                                <i class="fas fa-check"></i>
                            </div>
                            <p class="text-xs mt-2 font-semibold text-gray-400 peer-checked:text-[#f5c542] {{ $item->status == 'Selesai' ? 'text-[#f5c542]' : '' }}">Selesai</p>
                        </label>
                    </div>

                    <p class="text-center text-xs text-gray-500 mt-8 mb-4">*Pilih tahapan di atas, lalu klik tombol Simpan Perubahan di bawah.</p>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-800">
                        <a href="{{ route('homecare.index') }}" class="px-6 py-2.5 rounded-lg bg-[#2C2C2C] hover:bg-[#383838] text-gray-300 font-bold transition-all flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                        <button type="submit" class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-[#f5c542] to-[#e4a93c] hover:opacity-90 text-black font-bold shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </main>
</div>
@endsection