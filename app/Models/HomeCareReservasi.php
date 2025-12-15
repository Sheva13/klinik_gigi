<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeCareReservasi extends Model
{
    use HasFactory;

    protected $table = 'homecare_reservasi';

    protected $fillable = [
        'id',
        'no_pemeriksaan',
        'no_antrian',
        'pasien_id',
        'rekam_medis_id',
        'dokter_id',
        'jadwal_id',
        'tanggal_pesan',
        'waktu_pesan',
        'jam_mulai',
        'jam_selesai',
        'keluhan',
        'jenis_keluhan',
        'jenis_keluhan_lainnya',
        'biaya_reservasi',
        'promo_id',
        'potongan_promo',
        'biaya_transport',
        'pembayaran_total',
        'metode_pembayaran',
        'status',
        'status_reservasi',
        'status_booking', // Renamed from status_pembayaran
        'jenis_pasien',
        'alamat_lengkap',
        'latitude',
        'longitude',
        'tipe_layanan',
        'snap_token',
        'redirect_url',
        'status_pelunasan',
        'snap_token_pelunasan',

        'total_biaya_tindakan',
        'promo_id',
        'potongan_promo',
    ];

    public function rekamMedis()
    {
        return $this->belongsTo(RekamMedis::class, 'rekam_medis_id', 'id');
    }

    public function pasien()
    {
        return $this->belongsTo(RekamMedis::class, 'pasien_id', 'rekam_medis');
    }

    public function dokter()
    {
        return $this->belongsTo(MasterDokter::class, 'dokter_id', 'kode_dokter');
    }

    public function jadwalHarian()
    {
        return $this->belongsTo(JadwalHarian::class, 'jadwal_id', 'id');
    }

    public function masterJadwal()
    {
        return $this->belongsTo(MasterJadwal::class, 'jadwal_id', 'id');
    }

    public function tindakanPemeriksaan()
    {
        // After migration this will use 'homecare_reservasi_id'
        return $this->hasMany(TindakanPemeriksaan::class, 'homecare_reservasi_id', 'id');
    }

    public function biayaTambahan()
    {
        return $this->hasMany(BiayaTambahan::class, 'homecare_reservasi_id', 'id');
    }

    public function tracking()
    {
        return $this->hasMany(HomeCareTracking::class, 'id_periksa', 'id');
    }

    public function promo()
    {
        return $this->belongsTo(MasterPromo::class, 'promo_id', 'id');
    }

    // Trait methods implemented (migrated from consolidated HomeCareService)
    public function isPaid(): bool
    {
        return $this->status_booking === 'lunas';
    }

    public function isPendingPayment(): bool
    {
        return $this->status_booking === 'belum_lunas';
    }

    // Deprecated in new schema, mapped to 'belum_lunas' check
    public function isAwaitingVerification(): bool
    {
        return $this->status_booking === 'belum_lunas';
    }

    // Deprecated in new schema
    public function isVerified(): bool
    {
        return $this->status_booking === 'lunas';
    }

    public function getTotal(): float
    {
        return (float) $this->pembayaran_total;
    }

    public function getServiceCost(): float
    {
        return (float) ($this->biaya_reservasi ?? 0);
    }

    public function getRemainingPayment(): float
    {
        $paid = $this->biayaTambahan()
            ->where('komponen', 'UANG_MUKA')
            ->sum('biaya');

        return max(0, $this->pembayaran_total - $paid);
    }

    public function getDownPayment(): float
    {
        return (float) ($this->biayaTambahan()
            ->where('komponen', 'UANG_MUKA')
            ->sum('biaya') ?? 0);
    }

    public function isCancellable(): bool
    {
        return !$this->isPaid() && !in_array(strtolower($this->status_reservasi), ['selesai', 'dibatalkan']);
    }

    public function cancel(): void
    {
        $this->status_reservasi = 'dibatalkan';
        $this->status = 'Dibatalkan';
        $this->status_booking = 'gagal';
        $this->save();
    }

    public function markAsAwaitingVerification(): void
    {
        // No longer distinct status, keep as belum_lunas
        $this->status_booking = 'belum_lunas';
        $this->save();
    }

    public function markAsPaid(): void
    {
        $this->status_booking = 'lunas';
        $this->status = 'Menunggu Dokter';
        $this->status_reservasi = 'menunggu';
        $this->save();
    }
}