# Ringkasan Perubahan: Memindahkan Biaya Reservasi dari Hardcode ke Database

## Tujuan Perubahan
Perubahan ini bertujuan untuk memindahkan nilai biaya reservasi klinik yang sebelumnya di-hardcode (25000) ke dalam database, khususnya ke tabel `master_biaya_layanan`. Ini merupakan perubahan konseptual yang membuat sistem lebih fleksibel dan mudah dikelola tanpa perlu merubah kode aplikasi setiap kali ada perubahan biaya.

## Tabel master_biaya_layanan
Tabel ini dibuat untuk menyimpan referensi biaya berdasarkan kombinasi `tipe_layanan` dan `jenis_pasien`. Struktur tabel:
- id: Primary key
- tipe_layanan: Jenis layanan ('klinik', 'homecare')
- jenis_pasien: Jenis pasien ('Umum', 'BPJS', 'Asuransi')
- biaya_reservasi: Biaya dalam format mata uang
- created_at, updated_at: Timestamp

## Bagian yang Diubah
1. **Seeder**: Menambahkan data awal untuk biaya klinik (25000) dan homecare (75000)
2. **TransaksiReservasiController**: Mengganti hardcode biaya 25000 dengan pengambilan dari tabel master
3. **Model dan Service**: Menambahkan fungsi untuk mengambil biaya dari tabel master

## Implementasi
Perubahan dilakukan pada TransaksiReservasiController di fungsi createReservasi, di mana sebelumnya:

```php
$biaya = ($validated['metode_pembayaran'] === 'Midtrans') ? 25000 : 0;
```

Diubah menjadi:

```php
// Ambil biaya dari tabel master berdasarkan tipe layanan dan jenis pasien
$biayaFromMaster = $this->reservasiService->getBiayaReservasiForPreview('klinik', $validated['jenis_pasien']);

if ($biayaFromMaster === null) {
    // Jika biaya tidak ditemukan di tabel master, kembalikan error
    DB::rollback();
    return $this->errorResponse('Biaya layanan tidak ditemukan untuk kombinasi layanan dan jenis pasien yang dipilih', null, 422);
} else {
    // Gunakan biaya dari tabel master
    $biaya = $biayaFromMaster;

    // Jika metode pembayaran bukan Midtrans, biaya dianggap 0 (gratis)
    if ($validated['metode_pembayaran'] !== 'Midtrans') {
        $biaya = 0;
    }
}
```

## Manfaat
- Fleksibilitas: Biaya dapat diubah tanpa update aplikasi
- Konsistensi: Semua biaya dikelola dari satu sumber (database)
- Skalabilitas: Mudah menambahkan kombinasi layanan dan jenis pasien baru
- Konsep: Memisahkan konfigurasi (biaya) dari kode program