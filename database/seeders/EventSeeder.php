<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Club;
use App\Models\User;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Dapatkan Klub pertama (dari ClubSeeder) untuk menjadi penyelenggara
        $club = Club::first();

        // 2. Dapatkan Pengurus IMI pertama (admin) untuk menjadi pembuat
        //    Jika tidak ada, buat satu
        $adminUser = User::where('role', 'pengurus_imi')->first();
        if (!$adminUser) {
            $adminUser = User::factory()->create([
                'name' => 'Admin IMI Seeder',
                'email' => 'admin_seeder@imi.com',
                'role' => 'pengurus_imi'
            ]);
        }

        // 3. Pastikan klub ada sebelum membuat event
        if ($club) {
            Event::create([
                'event_name' => 'Kejurda Balap Motor IMI Sumut Seri 1',
                'event_date' => now()->addDays(30), // 30 hari dari sekarang
                'location' => 'Sirkuit Pancing, Medan',
                'description' => 'Kejuaraan Daerah (KEJURDA) Balap Motor putaran pertama musim 2025.',
                'proposing_club_id' => $club->id,
                'created_by_user_id' => $adminUser->id,
                'is_published' => true, // Langsung terbit
            ]);

            Event::create([
                'event_name' => 'Sumut Rally Championship 2025',
                'event_date' => now()->addDays(60), // 60 hari dari sekarang
                'location' => 'Perkebunan Rambong Sialang',
                'description' => 'Kejuaraan Rally mobil regional Sumatera Utara.',
                'proposing_club_id' => $club->id,
                'created_by_user_id' => $adminUser->id,
                'is_published' => true, // Langsung terbit
            ]);
        }
    }
}