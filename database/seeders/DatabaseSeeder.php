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
        
        User::factory()->create([
            'name' => 'Admin IMI',
            'email' => 'admin@imi.com',
            'role' => 'pengurus_imi' 
        ]);

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@imi.com',
            'role' => 'super_admin' 
        ]);
        
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'pembalap' 
        ]);
        
        // Urutan ini sudah benar
        $this->call([
            ClubSeeder::class,
            KisCategorySeeder::class,
            EventSeeder::class,
            RaceResultTestSeeder::class,
        ]);
    }
}