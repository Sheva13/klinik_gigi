@extends('layouts.adminlte')

@section('title', 'Dashboard')

@section('styles')
<style>
    /* Header & Notifications */
    .header-content {
        border-bottom: 1px solid #333333;
        padding-bottom: 1.5rem;
    }
    .btn-notification {
        background-color: #1A1A1A;
        border: none;
        color: #E0E0E0;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-notification:hover {
        background-color: #2a2a2a;
        color: #E0E0E0;
    }
    .notification-badge {
        background-color: #f5c542 !important; /* Warna kuning Figma */
        width: 8px;
        height: 8px;
        right: 5px !important; /* Sesuaikan posisi badge */
        top: 5px !important;
        border: 2px solid #1A1A1A; /* Tambah border agar lebih terlihat di background 1A1A1A */
    }
    .profile-picture {
        border-radius: 50%;
        width: 40px;
        height: 40px;
        background-size: cover;
        /* Ganti URL gambar profil ke yang lebih realistis jika perlu */
        background-image: url('{{ asset('assets/images/user-profile.png') }}'); /* Ganti dengan path gambar profil default Anda */
        background-color: #555;
        border: 2px solid #f5c542; /* Border kuning yang ada di Figma */
        cursor: pointer;
    }

    /* Card Menu Styling */
    .card-menu {
        background-color: #1A1A1A;
        border-radius: 12px;
        padding: 40px 30px; /* Padding yang lebih besar untuk tinggi yang sesuai */
        min-height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #333333; /* Border tipis */
        color: #E0E0E0; /* Warna teks default card */
    }
    .card-menu:hover {
        transform: translateY(-5px); 
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5); 
        color: #f5c542; /* Warna teks hover */
    }
    .card-menu:hover .material-symbols-outlined {
        color: #f5c542;
    }
    .card-menu .material-symbols-outlined {
        color: #f5c542; /* Warna ikon kuning */
        font-size: 48px; 
        margin-bottom: 15px;
        font-variation-settings: 'FILL' 0,'wght' 500,'GRAD' 0,'opsz' 48; 
        /* Pastikan icon default di non-dashboard card juga kuning */
    }
    .card-menu h3 {
        color: #E0E0E0; /* Warna judul card */
        font-weight: 700;
        font-size: 1.25rem;
        margin-bottom: 0;
    }
    .card-menu-dashboard {
        border: 2px solid #f5c542; /* Border kuning yang lebih tebal untuk card Dashboard aktif */
    }
    /* Pastikan warna teks di card Dashboard tetap E0E0E0 */
    .card-menu-dashboard h3, .card-menu-dashboard .material-symbols-outlined {
        color: #E0E0E0 !important;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">

    {{-- Header --}}
    <header class="d-flex justify-content-between align-items-center mb-5 header-content">
        <h1 class="fw-bolder">Dashboard</h1>

        <div class="d-flex align-items-center gap-3">
            {{-- Ganti div profile-picture dengan img atau elemen lain yang lebih sesuai --}}
            <div class="d-flex align-items-center gap-3 mb-4 sidebar-header" style="padding-left: 0; padding-right: 0;"> 
                {{-- LOGO dari folder public/assets/images --}}
                <img src="{{ asset('assets/images/profile/wais.jpg') }}" 
                     alt="3K Dental Care Logo" 
                     style="width:60px;height:60px;object-fit:cover;border-radius:50%;"/>
            </div>
    </header>

    {{-- Cards --}}
    <div class="row g-4">

        @php
            // Memastikan data item sesuai dengan urutan, ikon, dan label di gambar
            $items = [
                // Mengganti icon 'dashboard' dengan 'widgets' agar lebih sesuai dengan tampilan 4 kotak kecil
                ['icon' => 'widgets', 'title' => 'Dashboard', 'url' => '/dashboard', 'class' => 'card-menu-dashboard'], 
                // Icon 'Promo'
                ['icon' => 'sell', 'title' => 'Promo', 'url' => '/promo', 'class' => ''], 
                // Icon 'Dental Care'
                ['icon' => 'tooth', 'title' => 'Dental Care', 'url' => '/dental-care', 'class' => ''],
                // Icon 'Jadwal Praktek'
                ['icon' => 'calendar_today', 'title' => 'Jadwal Praktek', 'url' => '/jadwal', 'class' => ''],
                // Icon 'Dokter'
                ['icon' => 'medical_services', 'title' => 'Dokter', 'url' => '/dokter', 'class' => ''],
                // Icon 'Logout'
                ['icon' => 'logout', 'title' => 'Logout', 'url' => '/logout', 'class' => ''],
            ];
        @endphp

        @foreach ($items as $item)
            {{-- Gunakan col-4 agar menjadi 3 kolom (2 kolom di layar kecil sudah dihandel oleh Bootstrap) --}}
            <div class="col-6 col-lg-4"> 
                <a href="{{ $item['url'] }}" class="text-decoration-none">
                    {{-- Hapus text-white jika sudah diatur di CSS .card-menu --}}
                    <div class="card-menu {{ $item['class'] }}">
                        <span class="material-symbols-outlined">
                            {{ $item['icon'] }}
                        </span>
                        <h3>{{ $item['title'] }}</h3>
                    </div>
                </a>
            </div>
        @endforeach

    </div>

</div>

@endsection