<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3K Dental Care - Admin Login</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Reset body */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Manrope', sans-serif;
            background-color: #121212;
        }

        /* WRAPPER UTAMA: Background Gelap & Efek Radial */
        .main-wrapper {
            background-color: #121212;
            background-image: 
                radial-gradient(circle at 100% 0%, rgba(64, 52, 20, 0.2) 0%, transparent 40%),
                radial-gradient(circle at 0% 100%, rgba(64, 52, 20, 0.2) 0%, transparent 40%);
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* --- CARD STYLE (BARU) --- */
        .login-card {
            width: 100%;
            max-width: 450px; /* Sedikit diperlebar agar lega */
            padding: 40px 30px; /* Padding lebih besar */
            
            /* Memberikan warna background yang berbeda dari body */
            background-color: #1E1E1E; 
            
            /* Efek visual Card */
            border-radius: 24px; /* Sudut melengkung */
            border: 1px solid rgba(255, 215, 0, 0.15); /* Border emas tipis */
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6); /* Bayangan lembut */
        }

        /* Logo Styling */
        .logo-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #333;
            object-fit: cover; /* Agar gambar proporsional */
            margin: 0 auto 20px auto;
            display: block;
            border: 2px solid #333;
        }

        /* Text Gold Gradient */
        .text-gold {
            background: linear-gradient(to right, #FFD700, #D4AF37);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        /* Input Customization */
        .custom-input {
            background-color: #121212 !important; /* Lebih gelap dari card */
            border: 1px solid rgba(161, 98, 7, 0.3) !important;
            color: white !important;
            border-radius: 50px !important;
            padding: 14px 24px !important;
            font-size: 0.95rem;
        }

        .custom-input:focus {
            border-color: #f5c542 !important;
            box-shadow: 0 0 0 0.25rem rgba(245, 197, 66, 0.15) !important;
            background-color: #000000 !important;
            color: white !important;
        }
        
        .custom-input::placeholder {
            color: #555 !important;
        }

        /* Button Customization */
        .btn-gold {
            background-image: linear-gradient(to right, #FFD700, #D4AF37);
            border: none;
            border-radius: 50px;
            color: black;
            font-weight: 700;
            padding: 14px;
            width: 100%;
            margin-top: 15px;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: transform 0.2s;
        }
        
        .btn-gold:hover {
            background-image: linear-gradient(to right, #FFDF33, #E5C148);
            color: black;
            transform: translateY(-2px); /* Efek naik saat hover */
        }
    </style>
</head>
<body>

    <div class="main-wrapper">
        <div class="login-card text-center">
            
            <div class="mb-5">
                <img src="{{ asset('assets/images/logo.png') }}" 
                     alt="Logo Klinik" 
                     class="logo-circle">
                
                <h2 class="text-gold mb-1">HALAMAN ADMIN</h2>
                <p class="text-secondary small">K3 DENTAL CARE SEMARANG</p>
            </div>

            <form action="{{ route('auth.login') }}" method="POST">
                @csrf

                <div class="mb-3 text-start">
                    <input type="text" 
                           name="identifier" 
                           class="form-control custom-input @error('identifier') is-invalid @enderror" 
                           placeholder="Username" 
                           value="{{ old('identifier') }}" 
                           required>
                    
                    @error('identifier')
                        <div class="small text-danger mt-1 ms-3">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-4 text-start">
                    <input type="password" 
                           name="password" 
                           class="form-control custom-input @error('password') is-invalid @enderror" 
                           placeholder="Password" 
                           required>

                    @error('password')
                        <div class="small text-danger mt-1 ms-3">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-gold shadow-sm">
                    Login
                </button>

            </form>
        </div>
    </div>

</body>
</html>