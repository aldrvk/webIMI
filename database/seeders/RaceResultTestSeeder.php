<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Club;
use App\Models\Event;
use App\Models\KisCategory;
use App\Models\KisApplication;
use App\Models\KisLicense;
use App\Models\EventRegistration;
use Carbon\Carbon;

class RaceResultTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Menjalankan Skenario Seeder untuk Tes Hasil Lomba...');

        // 1. CARI AKTOR YANG ADA
        $adminUser = User::where('role', 'pengurus_imi')->first();
        // Kita gunakan klub dari seeder Anda sebelumnya
        $penyelenggaraClub = Club::where('nama_klub', 'SPEED\'ER MOTORSPORT')->first(); 
        $kategoriBalap = KisCategory::where('kode_kategori', 'C1')->first(); // Kita tes "Kelas C1"

        if (!$adminUser || !$penyelenggaraClub || !$kategoriBalap) {
            $this->command->error('Gagal seeding: Pastikan User Admin, Klub, dan Kategori KIS sudah ada.');
            return;
        }

        // 2. BUAT 1 USER PENYELENGGARA EVENT
        $penyelenggaraUser = User::create([
            'name' => 'Penyelenggara Speeder',
            'email' => 'penyelenggara@speeder.com',
            'password' => 'password', // Password akan otomatis di-hash oleh Model
            'role' => 'penyelenggara_event',
            'club_id' => $penyelenggaraClub->id, // Tautkan ke klub-nya
        ]);
        $this->command->info('User Penyelenggara (penyelenggara@speeder.com) dibuat.');

        // 3. BUAT 5 PEMBALAP & SIMULASIKAN KIS AKTIF
        $pembalapList = collect(); // Kita kumpulkan pembalap di sini
        $this->command->info('Membuat 5 pembalap tes dengan KIS aktif...');
        for ($i = 1; $i <= 5; $i++) {
            $pembalap = User::create([
                'name' => "Pembalap Tes $i",
                'email' => "pembalap_tes_$i@gmail.com",
                'password' => 'password',
                'role' => 'pembalap',
            ]);

            // Buat aplikasi KIS yang sudah disetujui
            $app = KisApplication::create([
                'pembalap_user_id' => $pembalap->id,
                'kis_category_id' => $kategoriBalap->id,
                'status' => 'Approved',
                'processed_by_user_id' => $adminUser->id,
                'approved_at' => now(),
            ]);

            // Buat Lisensi KIS yang aktif
            KisLicense::create([
                'pembalap_user_id' => $pembalap->id,
                'kis_category_id' => $kategoriBalap->id,
                'application_id' => $app->id,
                'kis_number' => "TEST-KIS-00$i",
                'issued_date' => now()->subMonth(),
                'expiry_date' => now()->addYear(), // Aktif selama 1 tahun
            ]);
            
            $pembalapList->push($pembalap);
        }

        // 4. BUAT 1 EVENT MASA LALU (AGAR BISA DI-INPUT HASILNYA)
        $eventDate = now()->subWeek(); // 1 minggu yang lalu
        $deadline = $eventDate->copy()->subDays(2)->endOfDay(); // Deadline 2 hari sebelum event

        $testEvent = Event::create([
            'event_name' => 'Kejuaraan Tes Khusus (Input Hasil)',
            'event_date' => $eventDate,
            'registration_deadline' => $deadline,
            'location' => 'Sirkuit Tes, Medan',
            'description' => 'Event ini dibuat oleh seeder untuk menguji input hasil lomba.',
            'biaya_pendaftaran' => 100000,
            'created_by_user_id' => $adminUser->id,
            'proposing_club_id' => $penyelenggaraClub->id, // Diselenggarakan oleh klub ini
            'is_published' => true,
        ]);
        
        // Tautkan event ini ke "Kelas C1"
        $testEvent->kisCategories()->attach($kategoriBalap->id);

        // 5. DAFTARKAN 5 PEMBALAP KE EVENT INI
        foreach ($pembalapList as $pembalap) {
            EventRegistration::create([
                'event_id' => $testEvent->id,
                'pembalap_user_id' => $pembalap->id,
                'kis_category_id' => $kategoriBalap->id,
                // Kita tidak set 'status' karena kita menjeda fitur pembayaran
                // 'result_position' dan 'points_earned' default-nya NULL/0
            ]);
        }

        $this->command->info('Skenario Tes Hasil Lomba berhasil dibuat!');
    }
}