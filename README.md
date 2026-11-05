# Klinik Gigi - Web Admin & API

Aplikasi Laravel untuk panel admin dan API aplikasi mobile klinik gigi.

## Fitur

- Dashboard admin
- Reservasi dan antrian
- Home care
- Dokter dan jadwal praktik
- Promo
- Data user dan settings
- API untuk aplikasi Flutter

## Menjalankan

1. `composer install`
2. Salin `.env.example` menjadi `.env`, lalu sesuaikan koneksi database
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `php artisan storage:link`
6. `php artisan serve`

Panel admin tersedia di `/admin/login`.
