<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            MasterPoliSeeder::class,
            MasterDokterSeeder::class,
            MasterJadwalSeeder::class,
            JadwalHarianSeeder::class,
            RekamMedisSeeder::class,
            UsersSeeder::class,
            MasterTindakanSeeder::class,
            PromoSeeder::class,
            MasterSpesialisSeeder::class,
<<<<<<< HEAD
            MasterBiayaLayananSeeder::class,
=======
            SettingsSeeder::class,
>>>>>>> adf7eb49649fdd4d32af9f0e0733659c5b70de14
        ]);
        // User::factory(10)->create();

      /*  User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]); */
    }
}
