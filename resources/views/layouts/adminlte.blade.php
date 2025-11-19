<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Admin Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

    <style>
        /* Umum */
        body { 
            font-family: 'Manrope', sans-serif; 
            background-color: #121212;
            color: #E0E0E0;
        }
        main {
            background-color: #121212;
            min-height: 100vh;
        }
        h1, h5 {
            color: #E0E0E0 !important;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px; 
            height: 100vh; 
            background-color: #1A1A1A;
            position: sticky; 
            top: 0;
            border-right: 1px solid #333333;
            /* P-4 di HTML memberikan padding 1.5rem (24px) di semua sisi */
        }
        .sidebar-header {
            border-bottom: 1px solid #333333;
            padding-bottom: 1rem !important;
            /* Pastikan ada padding horizontal untuk header jika belum ada di HTML */
            padding-left: 1rem; 
            padding-right: 1rem;
        }
        
        /* Navigasi: Navigasi itu sendiri yang memegang padding horizontal */
        .sidebar nav {
            padding: 0 1rem; /* Padding horizontal 1rem (16px) */
        }

        /* Link di dalam navigasi: Hapus padding horizontal di link, tapi pertahankan vertical */
        .sidebar nav a {
            color: #ccc; 
            text-decoration: none; 
            padding: 10px 15px; /* Padding vertical 10px, horizontal 15px untuk hover/normal */
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            gap: 15px;
            font-weight: 500;
            margin: 0 -15px; /* Margin negatif untuk meng-cover padding nav saat hover */
            width: calc(100% + 30px); /* 100% dari nav + 2x margin negatif */
        }
        
        .sidebar nav a:hover { 
            background-color: rgba(245, 197, 66, 0.1); 
            color: #f5c542;
        }
        
        /* Style untuk item menu yang aktif */
        .sidebar .active {
            background-color: rgba(245,197,66,0.2); 
            color: #f5c542 !important;
            font-weight: 700;
            /* Margin dan Padding sudah diatur di .sidebar nav a, tidak perlu diubah lagi */
        }
        
        .sidebar .active .material-symbols-outlined {
            color: #f5c542 !important;
        }
        
        .sidebar .material-symbols-outlined {
             font-variation-settings: 'FILL' 0,'wght' 500,'GRAD' 0,'opsz' 24;
             color: #ccc;
        }

        /* Footer */
        .sidebar-footer {
             border-top: 1px solid #333333;
             margin-top: 1rem;
             padding: 1rem 1rem 0 1rem; /* Padding atas dan horizontal */
        }
        .sidebar-footer a {
             color: #ccc; 
             text-decoration: none; 
             padding: 10px 15px;
             border-radius: 8px; 
             display: flex; 
             align-items: center; 
             gap: 15px;
             font-weight: 500;
             margin: 0 -15px;
             width: calc(100% + 30px);
        }
        .sidebar-footer a:hover {
            background-color: rgba(245, 197, 66, 0.1); 
            color: #f5c542;
        }
        
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24;
        }
    </style>
    @yield('styles')
</head>

<body>

<div class="d-flex">

    {{-- Sidebar --}}
    <aside class="sidebar p-4 d-flex flex-column justify-content-between">
        <div>
            {{-- Hapus p-4 dari aside dan pindahkan ke header untuk konsistensi --}}
            <div class="d-flex align-items-center gap-3 mb-4 sidebar-header" style="padding-left: 0; padding-right: 0;"> 
                {{-- LOGO dari folder public/assets/images --}}
                <img src="{{ asset('assets/images/logo.png') }}" 
                     alt="3K Dental Care Logo" 
                     style="width:40px;height:40px;object-fit:cover;border-radius:50%;"/>
                
                <div>
                    <h5 class="text-white fw-bold mb-0">3K Dental Care</h5>
                    <small class="text-secondary">Admin Panel</small>
                </div>
            </div>

            {{-- Navigasi diberi padding di CSS (.sidebar nav) --}}
            <nav class="d-flex flex-column gap-2"> 
                <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">dashboard</span> Dashboard
                </a>
                
                {{-- Perhatikan penambahan class 'text-warning' dihapus dari link promo, dental-care, jadwal, dokter --}}
                <a href="/promo" class="{{ request()->is('promo') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">bookmark_manager</span> Promo
                </a>
                <a href="/dental-care" class="{{ request()->is('dental-care') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">dentistry</span> Dental Care
                </a>
                <a href="/jadwal" class="{{ request()->is('jadwal') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">calendar_month</span> Jadwal Praktek
                </a>
                <a href="/dokter" class="{{ request()->is('dokter') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">health_and_safety</span> Dokter
                </a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <a href="/logout">
                <span class="material-symbols-outlined">logout</span> Logout
            </a>
        </div>
    </aside>

    {{-- Main Content --}}
    <main class="flex-grow-1 p-5">
        @yield('content')
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>