<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Club;
use App\Models\User;
use App\Models\KisCategory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Event::truncate();
        DB::table('event_kis_category')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $clubs = Club::all();
        $categories = KisCategory::all();
        $adminUser = User::where('role', 'pengurus_imi')->first();

        if ($clubs->isEmpty() || $categories->isEmpty() || !$adminUser) {
            $this->command->warn('Pastikan ClubSeeder, KisCategorySeeder, dan User Admin ada sebelum menjalankan EventSeeder.');
            return;
        }

        // Ambil ID Kategori
        $c1_c2 = $categories->whereIn('kode_kategori', ['C1', 'C2'])->pluck('id');
        $b1_b3 = $categories->whereIn('kode_kategori', ['B1', 'B3'])->pluck('id');
        $b5 = $categories->whereIn('kode_kategori', ['B5'])->pluck('id');
        $all_motor = $categories->where('tipe', 'Motor')->pluck('id');

        // Tentukan Tanggal
        $eventDate1 = Carbon::now()->subMonths(8);
        $eventDate2 = Carbon::now()->subMonths(5);
        $eventDate3 = Carbon::now()->addDays(20);
        $eventDate4 = Carbon::now()->addDays(45);
        $eventDate5 = Carbon::now()->addDays(70);

        $eventsList = [
            // === EVENT MASA LALU (PAST EVENTS) ===
            [
                'event_name' => 'Kejurda Road Race IMI Sumut 2024 (Seri 3)',
                'event_date' => $eventDate1,
                'registration_deadline' => $eventDate1->copy()->subDays(2)->endOfDay(), // 2 hari sebelum event
                'location' => 'Sirkuit Pancing, Medan',
                'description' => 'Seri final Kejuaraan Daerah Balap Motor 2024.',
                'biaya_pendaftaran' => 350000,
                'kontak_panitia' => '08123456001 (Panitia Road Race)',
                'url_regulasi' => 'https://imi-sumut.or.id/regulasi/2024/roadrace-seri3.pdf',
                'categories' => $c1_c2,
            ],
            [
                'event_name' => 'Sumatera Rally Championship 2024 (Putaran 2)',
                'event_date' => $eventDate2,
                'registration_deadline' => $eventDate2->copy()->subDays(5)->endOfDay(), // 5 hari sebelum event
                'location' => 'Perkebunan Aek Nauli, Simalungun',
                'description' => 'Kejuaraan rally putaran kedua.',
                'biaya_pendaftaran' => 2500000,
                'kontak_panitia' => '08123456002 (Panitia Rally)',
                'url_regulasi' => 'https://imi-sumut.or.id/regulasi/2024/rally-seri2.pdf',
                'categories' => $b1_b3,
            ],
            
            // === EVENT MASA DEPAN (UPCOMING EVENTS) ===
            [
                'event_name' => 'Kejurda Balap Motor IMI Sumut 2025 (Seri 1)',
                'event_date' => $eventDate3,
                'registration_deadline' => $eventDate3->copy()->subDays(2)->setHour(17)->setMinute(0), // 2 hari sebelum, jam 5 sore
                'location' => 'Sirkuit Pancing, Medan',
                'description' => 'Seri pembuka Kejuaraan Daerah (KEJURDA) Balap Motor musim 2025.',
                'biaya_pendaftaran' => 400000,
                'kontak_panitia' => '08123456001 (Panitia Road Race)',
                'url_regulasi' => 'https://imi-sumut.or.id/regulasi/2025/roadrace-seri1.pdf',
                'categories' => $all_motor,
            ],
             [
                'event_name' => 'IMI Sumut Karting Prix 2025',
                'event_date' => $eventDate4,
                'registration_deadline' => $eventDate4->copy()->subDays(3)->endOfDay(), // 3 hari sebelum, 23:59
                'location' => 'Sirkuit Gokart IMI Sumut, Pancing',
                'description' => 'Kejuaraan gokart junior dan senior.',
                'biaya_pendaftaran' => 750000,
                'kontak_panitia' => '08123456005 (Panitia Karting)',
                'url_regulasi' => null,
                'categories' => $b5,
            ],
            [
                'event_name' => 'Sumut Rally Championship 2025 (Seri 1)',
                'event_date' => $eventDate5,
                'registration_deadline' => $eventDate5->copy()->subDays(5)->endOfDay(), // 5 hari sebelum
                'location' => 'Perkebunan Rambong Sialang, Serdang Bedagai',
                'description' => 'Seri pembuka kejuaraan rally mobil regional Sumatera Utara.',
                'biaya_pendaftaran' => 3000000,
                'kontak_panitia' => '08123456002 (Panitia Rally)',
                'url_regulasi' => 'https://imi-sumut.or.id/regulasi/2025/rally-seri1.pdf',
                'categories' => $b1_b3,
            ],
        ];

        $this->command->info('Membuat data event palsu...');
        foreach ($eventsList as $eventData) {
            
            $categoriesToAttach = $eventData['categories'];
            unset($eventData['categories']);
            
            $eventData['proposing_club_id'] = $clubs->random()->id;
            $eventData['created_by_user_id'] = $adminUser->id;
            $eventData['is_published'] = true;

            $event = Event::create($eventData);

            if ($event && $categoriesToAttach) {
                $event->kisCategories()->attach($categoriesToAttach);
            }
        }
        $this->command->info(count($eventsList) . ' event baru berhasil dibuat (dengan deadline).');
    }
}
