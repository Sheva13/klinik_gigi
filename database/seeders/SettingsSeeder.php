<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'clinic_lat',
                'value' => '-7.0005141',
                'description' => 'Latitude lokasi klinik',
            ],
            [
                'key' => 'clinic_lng',
                'value' => '110.4250683',
                'description' => 'Longitude lokasi klinik',
            ],
            [
                'key' => 'price_per_km',
                'value' => '1750',
                'description' => 'Harga per kilometer (searah)',
            ],
            [
                'key' => 'homecare_base_fee',
                'value' => '35000',
                'description' => 'Biaya dasar layanan Home Care',
            ],
            [
                'key' => 'reservation_fee',
                'value' => '25000',
                'description' => 'Biaya reservasi konsultasi dokter',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
