<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Admin Dashboard')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('dist/css/adminreservasi-custom.css') }}">
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
        }
        .sidebar-header {
            border-bottom: 1px solid #333333;
            padding-bottom: 1rem !important;
            padding-left: 1rem; 
            padding-right: 1rem;
        }
        
        .sidebar nav {
            padding: 0 1rem;
        }

        .sidebar nav a {
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
        
        .sidebar nav a:hover { 
            background-color: rgba(245, 197, 66, 0.1); 
            color: #f5c542;
        }
        
        .sidebar .active {
            background-color: rgba(245,197,66,0.2); 
            color: #f5c542 !important;
            font-weight: 700;
        }
        
        .sidebar .active .material-symbols-outlined {
            color: #f5c542 !important;
        }
        
        .sidebar .material-symbols-outlined {
             font-variation-settings: 'FILL' 0,'wght' 500,'GRAD' 0,'opsz' 24;
             color: #ccc;
        }

        .sidebar-footer {
             border-top: 1px solid #333333;
             margin-top: 1rem;
             padding: 1rem 1rem 0 1rem;
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
             cursor: pointer; /* Tambahan pointer biar berasa tombol */
        }
        .sidebar-footer a:hover {
            background-color: rgba(245, 197, 66, 0.1); 
            color: #f5c542;
        }

    </style>
    @yield('styles')
</head>

<body>

<div class="d-flex">

    {{-- Sidebar --}}
    <aside class="sidebar p-4 d-flex flex-column justify-content-between">
        <div>
            <div class="d-flex align-items-center gap-3 mb-4 sidebar-header" style="padding-left: 0; padding-right: 0;"> 
                <img src="{{ asset('assets/images/logo.png') }}" 
                     alt="3K Dental Care Logo" 
                     style="width:40px;height:40px;object-fit:cover;border-radius:50%;"/>
                
                <div>
                    <h5 class="text-white fw-bold mb-0">3K Dental Care</h5>
                    <small class="text-secondary">Admin Panel</small>
                </div>
            </div>

            <nav class="d-flex flex-column gap-2"> 

                {{-- FIX: Arahkan ke admin.dashboard sesuai web.php --}}
                <a href="{{ route('admin.dashboard') }}" 
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">dashboard</span> Dashboard
                </a>
                
                {{-- Promo --}}
                <a href="{{ route('promo.index') }}" 
                   class="{{ request()->is('promo*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">bookmark_manager</span> Promo
                </a>

                {{-- Dental Care (Pastikan Route ini ada atau biarkan link manual) --}}
                <a href="{{ route('homecare.index') }}" class="{{ request()->is('homecare*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">dentistry</span> Home Care
                </a>

                <a href="{{ route('jadwal.index') }}" class="{{ request()->is('jadwal*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">calendar_month</span> Jadwal Praktek
                </a>

                <a href="{{ route('dokter.index') }}" class="{{ request()->is('dokter*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">health_and_safety</span> Dokter
                </a>

                <a href="{{ route('reservasi.admin.index') }}" class="{{ request()->routeIs('reservasi.admin.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">event_note</span> Reservasi
                </a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">group</span> Data User
                </a>

                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">settings</span> Pengaturan
                </a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <a href="{{ route('admin.logout') }}"
   class="nav-link d-flex align-items-center"
   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">

    <i class="nav-icon fas fa-sign-out-alt"></i>
    <p class="mb-0 ml-2">Logout</p>

</a>



<form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
    @csrf
</form>
        </div>
    </aside>

    <main class="flex-grow-1 p-5">
        @yield('content')
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>