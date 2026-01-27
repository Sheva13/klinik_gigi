# Ringkasan Perubahan: Memindahkan Biaya dari Hardcode ke Database

## Tujuan Perubahan
Perubahan ini bertujuan untuk memindahkan nilai biaya reservasi yang sebelumnya di-hardcode di kode aplikasi ke dalam database, khususnya ke tabel `master_biaya_layanan`. Ini merupakan perubahan konseptual yang membuat sistem lebih fleksibel dan mudah dikelola tanpa perlu merubah kode aplikasi setiap kali ada perubahan biaya.

## Tabel master_biaya_layanan
Tabel ini dibuat untuk menyimpan referensi biaya berdasarkan kombinasi `tipe_layanan` dan `jenis_pasien`. Struktur tabel:
- id: Primary key
- tipe_layanan: Jenis layanan ('klinik', 'homecare')
- jenis_pasien: Jenis pasien ('Umum', 'BPJS', 'Asuransi')
- biaya_reservasi: Biaya dalam format mata uang
- created_at, updated_at: Timestamp

## Bagian Minimal yang Diubah
1. **Seeder**: Menambahkan data awal untuk biaya klinik (25000) dan homecare (75000)
2. **TransaksiReservasiController**: Mengganti hardcode biaya 25000 dengan pengambilan dari tabel master
3. **HomeCareService**: Mengganti hardcode biaya dengan pengambilan dari tabel master
4. **Model dan Service**: Menambahkan fungsi untuk mengambil biaya dari tabel master

## Implementasi Minimal
Perubahan hanya dilakukan pada bagian yang mengatur biaya reservasi, tanpa mengubah alur bisnis utama. Biaya tetap disimpan ke kolom `reservasi.biaya_reservasi` sesuai dengan struktur yang sudah ada, hanya saja nilainya sekarang diambil dari tabel master daripada di-hardcode.

## Manfaat
- Fleksibilitas: Biaya dapat diubah tanpa update aplikasi
- Konsistensi: Semua biaya dikelola dari satu sumber (database)
- Skalabilitas: Mudah menambahkan kombinasi layanan dan jenis pasien baru
- Konsep: Memisahkan konfigurasi (biaya) dari kode program