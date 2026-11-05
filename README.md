# 3K Dental Care - Web Admin & API

Panel admin dan REST API untuk aplikasi klinik gigi **3K Dental Care**. Repo ini
melayani dua kebutuhan sekaligus:

- Web admin berbasis Blade untuk operasional klinik.
- API JSON untuk aplikasi Flutter (pasien, reservasi, home care, pembayaran).

## Status Demo

| Bagian | URL | Keterangan |
| --- | --- | --- |
| Aplikasi Flutter (web) | <https://klinikgigi-wika.netlify.app> | Live, `DEMO_MODE=false` |
| Web admin | <https://web-production-3e9a.up.railway.app/admin/login> | Live, MySQL Railway |
| API | <https://web-production-3e9a.up.railway.app/api> | Live, `GET /api/check` |

Backend dan aplikasi Flutter web sudah terhubung ke API Railway. Mode demo
(`DEMO_MODE=true`) tetap tersedia untuk development tanpa backend.

## Akun Demo

| Role | Username / Email | Password |
| --- | --- | --- |
| Admin lokal | `Farrel` | `admin123` |
| Admin production | `Farrel` | diatur lewat `ADMIN_PASSWORD` |
| Pasien | `farrelsheva@gmail.com` | `password123` |

Akun admin bisa dioverride lewat `ADMIN_USERNAME` dan `ADMIN_PASSWORD` sebelum
menjalankan seeder. Password production tidak ditulis di repo.

## Fitur

- Dashboard admin dan autentikasi admin.
- Reservasi poliklinik, antrian, pembayaran, dan verifikasi pembayaran.
- Dental home care: jadwal, estimasi biaya, booking, tracking, dan settlement.
- Master dokter, poli, jadwal praktik, promo, dan biaya layanan.
- Data pasien, rekam medis, dan pengaturan aplikasi.
- REST API untuk Flutter: auth, OTP, profil, reservasi, home care, poin, promo,
  dan callback Midtrans.

## Arsitektur

```text
Flutter (Android/Web)
        |
        | REST /api (JSON + Sanctum token)
        v
Laravel 12  --->  MySQL
        ^
        |
Browser admin (Blade, /admin)
```

Web admin dan API memakai database yang sama. Flutter tidak mengakses database
secara langsung; semua lewat endpoint `/api`.

## Teknologi

- Laravel 12, PHP 8.3
- MySQL
- Blade + Bootstrap
- Laravel Sanctum
- Midtrans (sandbox)
- Docker untuk deployment

## Menjalankan Lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env`, lalu:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

- Web admin: <http://127.0.0.1:8000/admin/login>
- API: <http://127.0.0.1:8000/api>

## Deploy Backend (Railway)

Repo sudah menyertakan `Dockerfile`, `docker/entrypoint.sh`, dan `railway.json`.

1. Push branch `wika` ke GitHub.
2. Di Railway: **New Project -> Deploy from GitHub Repo**, pilih repo dan branch
   `wika`.
3. Tambahkan database **MySQL** dari Railway.
4. Isi variables dari `.env.production.example`. Nilai penting:
   - `APP_KEY`: hasil `php artisan key:generate --show`
   - `APP_URL`: domain Railway, misalnya `https://nama-app.up.railway.app`
   - `DB_*`: ambil dari service MySQL Railway
   - `ADMIN_USERNAME` dan `ADMIN_PASSWORD`
   - `MIDTRANS_*`: isi kredensial sandbox
5. Deploy pertama: set `RUN_MIGRATIONS=true` agar tabel dibuat otomatis.
   Setelah berhasil, set kembali `false`.
6. Jalankan seeder sekali dengan `RUN_SEED=true`, lalu set kembali `false`.
   Alternatif lewat Railway Shell:

   ```bash
   php artisan db:seed --force
   ```

7. Buka `/admin/login` dan login dengan akun admin.
8. Callback Midtrans:
   `https://web-production-3e9a.up.railway.app/api/payment/midtrans-callback`.

Catatan deployment:

- Filesystem Railway bersifat sementara. Volume sudah dipasang di
  `/var/www/html/storage/app/public` untuk upload foto; alternatifnya gunakan
  object storage seperti S3.
- Untuk demo, `QUEUE_CONNECTION=sync` dan `MAIL_MAILER=log` cukup. Untuk OTP
  produksi, gunakan SMTP/Mailtrap dan worker queue.
- `APP_DEBUG` wajib `false` di production.
- Railway menyuntikkan konfigurasi Apache saat runtime, jadi entrypoint
  menonaktifkan MPM ganda dan menyesuaikan Apache ke `$PORT`.

## Build Flutter yang Terhubung ke API

Android/emulator:

```bash
flutter build apk --release \
  --dart-define=DEMO_MODE=false \
  --dart-define=API_BASE_URL=https://web-production-3e9a.up.railway.app/api
```

Web:

```bash
flutter build web --release \
  --dart-define=DEMO_MODE=false \
  --dart-define=API_BASE_URL=https://web-production-3e9a.up.railway.app/api
```

Untuk development lokal:

- Emulator Android: `http://10.0.2.2:8000/api`
- HP fisik: `http://IP-LAN-PC:8000/api`
- Production: wajib HTTPS

## Endpoint API Utama

| Method | Endpoint | Fungsi |
| --- | --- | --- |
| POST | `/api/login` | Login pasien |
| POST | `/api/register` | Registrasi pasien |
| POST | `/api/auth/request-otp` | Kirim OTP |
| POST | `/api/auth/verify-otp` | Verifikasi OTP |
| GET | `/api/reservasi/poli` | Daftar poli |
| POST | `/api/reservasi/create` | Buat reservasi |
| GET | `/api/reservasi/riwayat/{id}` | Riwayat reservasi |
| GET | `/api/homecare/jadwal` | Jadwal home care |
| POST | `/api/homecare/calculate` | Estimasi biaya home care |
| POST | `/api/homecare/booking` | Booking home care |
| POST | `/api/payment/midtrans-callback` | Callback Midtrans |

## Struktur Singkat

```text
app/Http/Controllers/   controller web admin dan API
app/Models/             model Eloquent
database/migrations/    skema database
database/seeders/       data awal/demo
resources/views/        tampilan Blade admin
routes/web.php          route web admin
routes/api.php          route API Flutter
docker/                 konfigurasi Apache, PHP, entrypoint
```

## Checklist Portofolio

- [x] Deploy backend dan isi URL admin/API di README.
- [x] Ganti password admin default.
- [x] Isi data demo (dokter, jadwal, promo, reservasi).
- [ ] Tambahkan screenshot web admin dan aplikasi Flutter.
- [ ] Jelaskan arsitektur dan tantangan teknis di bagian case study.
- [ ] Pastikan `.env`, kunci Midtrans, dan database dump tidak ikut commit.
