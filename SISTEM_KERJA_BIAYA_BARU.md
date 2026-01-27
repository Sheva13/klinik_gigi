# Sistem Kerja Dinamis untuk Biaya Reservasi

## Penjelasan Sistem Kerja Dinamis (SISTEM KERJA BIAYA BARU)

### 1. **Tujuan Utama Sistem**
Mengganti biaya yang sebelumnya di-hardcode (25000 untuk klinik) menjadi sistem dinamis yang mengambil data dari database.

### 2. **Struktur Tabel Baru**
Dibuat tabel `master_biaya_layanan` dengan kolom:
- `tipe_layanan`: Jenis layanan ('klinik', 'homecare', dll)
- `jenis_pasien`: Jenis pasien ('Umum', 'BPJS', 'Asuransi')
- `biaya_reservasi`: Biaya yang ditentukan untuk kombinasi tersebut

### 3. **Alur Sistem Baru**

#### A. Saat User Membuat Reservasi Klinik:
1. **Flutter** mengirim data ke backend termasuk `tipe_layanan` dan `jenis_pasien`
2. **Backend (Laravel)** menerima data dan mencari biaya di tabel `master_biaya_layanan`
3. **Backend mencari** berdasarkan kombinasi:
   - `tipe_layanan = 'klinik'` 
   - `jenis_pasien = 'Umum'` (atau BPJS/Asuransi)
4. **Jika ditemukan**, ambil nilai `biaya_reservasi` dari database
5. **Jika tidak ditemukan**, sistem akan **mengembalikan error** (tidak ada fallback ke hardcode)
6. **Simpan** biaya ke tabel `reservasi.biaya_reservasi`

#### B. Contoh Data di Database:
```
| tipe_layanan | jenis_pasien | biaya_reservasi |
|--------------|--------------|-----------------|
| klinik       | Umum         | 25000           |
| klinik       | BPJS         | 20000           |
| homecare     | Umum         | 75000           |
```

### 4. **Perubahan di Backend (Laravel)**
```php
// SEBELUMNYA (hardcode):
$biaya = ($metode_pembayaran === 'Midtrans') ? 25000 : 0;

// SEKARANG (database):
$biayaFromMaster = $this->reservasiService->getBiayaReservasiForPreview('klinik', $jenis_pasien);

if ($biayaFromMaster === null) {
    // Kembalikan error jika tidak ditemukan di database
    return response error;
} else {
    $biaya = $biayaFromMaster;
}
```

### 5. **Perubahan di Frontend (Flutter)**
- **Hapus semua hardcode** seperti `25000`
- **Tambahkan dropdown** untuk memilih tipe layanan dan jenis pasien
- **Ambil biaya dari API** saat user memilih kombinasi
- **Tampilkan biaya secara real-time** tanpa reload halaman

### 6. **Keunggulan Sistem Baru**
- ✅ **Fleksibel**: Biaya bisa diubah tanpa update aplikasi
- ✅ **Dinamis**: Bisa tambah jenis layanan/pasien baru
- ✅ **Konsisten**: Semua biaya dari satu sumber (database)
- ✅ **Aman**: Tidak ada nilai rahasia di kode frontend
- ✅ **Siap Berkembang**: Mudah ditambah fitur baru

### 7. **Cara Testing**
1. Pastikan tabel `master_biaya_layanan` sudah ada datanya
2. Jalankan backend: `php artisan serve`
3. Di Flutter, pilih kombinasi layanan dan pasien
4. Biaya harus muncul sesuai dengan data di database

### 8. **Error Handling**
Jika data tidak ditemukan di database, sistem akan mengembalikan error ke Flutter, bukan menggunakan nilai default, sehingga memastikan semua kombinasi harga harus ada di database sebelum bisa digunakan.

Dengan sistem ini, semua biaya ditentukan oleh database, bukan oleh kode program, membuat sistem lebih fleksibel dan mudah dikelola.