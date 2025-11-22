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
        // Panggil seeder fondasi (WAJIB PALING ATAS)
        $this->call([
            ClubSeeder::class,
            KisCategorySeeder::class,
        ]);
        
        // Panggil seeder skenario lengkap kita
        // Seeder ini akan membuat semua User (Admin+Pembalap) dan Event
        $this->call([
            FullStorySeeder::class,
            SettingSeeder::class,
        ]);
    }
}