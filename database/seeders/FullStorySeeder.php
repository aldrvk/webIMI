<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Club;
use App\Models\Event;
use App\Models\KisCategory;
use App\Models\PembalapProfile;
use App\Models\KisApplication;
use App\Models\KisLicense;
use App\Models\EventRegistration;
use Carbon\Carbon;

class FullStorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Menjalankan Seeder Skenario Lengkap (Full Story)...');

        // ---------------------------------------------------------------
        // LANGKAH 1: Ambil Data Dasar (Fondasi)
        // ---------------------------------------------------------------
        
        $clubSpeeder = Club::where('nama_klub', 'SPEED\'ER MOTORSPORT')->first();
        $clubKitakita = Club::where('nama_klub', 'Kitakita Motorsport')->first();
        $clubImi = Club::where('nama_klub', 'IMI Sumut Official')->first();
        
        $catC1 = KisCategory::where('kode_kategori', 'C1')->first(); // Balap Motor
        $catB5 = KisCategory::where('kode_kategori', 'B5')->first(); // Karting

        if (!$clubSpeeder || !$clubKitakita || !$catC1 || !$catB5) {
            $this->command->error('Seeding Gagal: Pastikan ClubSeeder & KisCategorySeeder sudah dijalankan dan berisi data (SPEED\'ER, Kitakita, C1, B5).');
            return;
        }

        // ---------------------------------------------------------------
        // LANGKAH 2: Buat User untuk Setiap Role
        // ---------------------------------------------------------------
        $this->command->info('Membuat user untuk 5 role...');
        
        $superAdmin = User::create([
            'name' => 'Super Admin', 'email' => 'superadmin@imi.com', 'password' => 'password', 'role' => 'super_admin'
        ]);
        
        $pengurusIMI = User::create([
            'name' => 'Pengurus IMI', 'email' => 'pengurus@imi.com', 'password' => 'password', 'role' => 'pengurus_imi'
        ]);
        
        $pimpinanIMI = User::create([
            'name' => 'Pimpinan IMI', 'email' => 'pimpinan@imi.com', 'password' => 'password', 'role' => 'pimpinan_imi'
        ]);
        
        $penyelenggara = User::create([
            'name' => 'Penyelenggara Speeder', 'email' => 'penyelenggara@imi.com', 'password' => 'password', 'role' => 'penyelenggara_event', 'club_id' => $clubSpeeder->id
        ]);

        // ---------------------------------------------------------------
        // LANGKAH 3: Buat 3 Skenario Pembalap
        // ---------------------------------------------------------------
        $this->command->info('Membuat 3 skenario pembalap...');

        // === PEMBALAP A: "PEMBALAP LUNAS" (Untuk Tes Hasil Lomba) ===
        $pembalapLunas = User::create(['name' => 'Pembalap Lunas', 'email' => 'lunas@imi.com', 'password' => 'password', 'role' => 'pembalap']);
        PembalapProfile::create(['user_id' => $pembalapLunas->id, 'club_id' => $clubKitakita->id, 'tanggal_lahir' => '1990-01-01']);
        $appLunas = KisApplication::create(['pembalap_user_id' => $pembalapLunas->id, 'kis_category_id' => $catC1->id, 'status' => 'Approved', 'processed_by_user_id' => $pengurusIMI->id, 'approved_at' => now()]);
        KisLicense::create(['pembalap_user_id' => $pembalapLunas->id, 'application_id' => $appLunas->id, 'kis_category_id' => $catC1->id, 'kis_number' => 'TEST-KIS-001', 'issued_date' => now(), 'expiry_date' => now()->addYear()]);

        // === PEMBALAP B: "PEMBALAP PENDING" (Untuk Tes Validasi Bayar) ===
        $pembalapPending = User::create(['name' => 'Pembalap Pending', 'email' => 'pending@imi.com', 'password' => 'password', 'role' => 'pembalap']);
        PembalapProfile::create(['user_id' => $pembalapPending->id, 'club_id' => $clubKitakita->id, 'tanggal_lahir' => '1991-01-01']);
        $appPending = KisApplication::create(['pembalap_user_id' => $pembalapPending->id, 'kis_category_id' => $catC1->id, 'status' => 'Approved', 'processed_by_user_id' => $pengurusIMI->id, 'approved_at' => now()]);
        KisLicense::create(['pembalap_user_id' => $pembalapPending->id, 'application_id' => $appPending->id, 'kis_category_id' => $catC1->id, 'kis_number' => 'TEST-KIS-002', 'issued_date' => now(), 'expiry_date' => now()->addYear()]);

        // === PEMBALAP C: "PEMBALAP DITOLAK" (Untuk Tes Alur Ditolak) ===
        $pembalapDitolak = User::create(['name' => 'Pembalap Ditolak', 'email' => 'ditolak@imi.com', 'password' => 'password', 'role' => 'pembalap']);
        PembalapProfile::create(['user_id' => $pembalapDitolak->id, 'club_id' => $clubImi->id, 'tanggal_lahir' => '1992-01-01']);
        $appDitolak = KisApplication::create(['pembalap_user_id' => $pembalapDitolak->id, 'kis_category_id' => $catB5->id, 'status' => 'Approved', 'processed_by_user_id' => $pengurusIMI->id, 'approved_at' => now()]);
        KisLicense::create(['pembalap_user_id' => $pembalapDitolak->id, 'application_id' => $appDitolak->id, 'kis_category_id' => $catB5->id, 'kis_number' => 'TEST-KIS-003', 'issued_date' => now(), 'expiry_date' => now()->addYear()]);

        // ---------------------------------------------------------------
        // LANGKAH 4: Buat 2 Skenario Event
        // ---------------------------------------------------------------
        $this->command->info('Membuat 2 skenario event...');

        // === EVENT A: "EVENT SELESAI" (Untuk Tes Input Hasil) ===
        $eventSelesai = Event::create([
            'event_name' => 'Kejuaraan Tes (SELESAI)',
            'event_date' => Carbon::now()->subWeek(),
            'registration_deadline' => Carbon::now()->subWeek()->subDays(2)->endOfDay(),
            'location' => 'Sirkuit Pancing',
            'proposing_club_id' => $clubSpeeder->id, // <-- PERBAIKAN 1 (Menggunakan $clubSpeeder)
            'created_by_user_id' => $pengurusIMI->id,
            'is_published' => true,
            'biaya_pendaftaran' => 100000,
            'bank_account_info' => "BCA 12345 a/n Klub Speeder"
        ]);
        $eventSelesai->kisCategories()->attach($catC1->id);

        // === EVENT B: "EVENT AKAN DATANG" (Untuk Tes Pembayaran) ===
        $eventAkanDatang = Event::create([
            'event_name' => 'Kejuaraan Tes (AKAN DATANG)',
            'event_date' => Carbon::now()->addMonth(),
            'registration_deadline' => Carbon::now()->addWeeks(3)->endOfDay(),
            'location' => 'Sirkuit Karting IMI',
            'proposing_club_id' => $clubSpeeder->id, // <-- PERBAIKAN 2 (Menggunakan $clubSpeeder)
            'created_by_user_id' => $pengurusIMI->id,
            'is_published' => true,
            'biaya_pendaftaran' => 250000,
            'bank_account_info' => "Mandiri 98765 a/n Panitia Speeder",
            'image_banner_url' => 'event-posters/dummy-poster.jpg'
        ]);
        $eventAkanDatang->kisCategories()->attach([$catC1->id, $catB5->id]); // <-- PERBAIKAN 3 (Menggunakan $eventAkanDatang)

        // ---------------------------------------------------------------
        // LANGKAH 5: Buat Skenario Pendaftaran
        // ---------------------------------------------------------------
        $this->command->info('Membuat 3 skenario pendaftaran...');

        // Skenario 1: Pembalap Lunas, ikut event, dan Menang
        EventRegistration::create([
            'event_id' => $eventSelesai->id,
            'pembalap_user_id' => $pembalapLunas->id,
            'kis_category_id' => $catC1->id,
            'status' => 'Confirmed',
            'payment_proof_url' => 'payment-proofs/dummy-lunas.jpg',
            'payment_processed_at' => now(),
            'payment_processed_by_user_id' => $penyelenggara->id,
            'result_position' => 1,
            'points_earned' => 25
        ]);

        // Skenario 2: Pembalap Pending, daftar event akan datang
        EventRegistration::create([
            'event_id' => $eventAkanDatang->id,
            'pembalap_user_id' => $pembalapPending->id,
            'kis_category_id' => $catC1->id,
            'status' => 'Pending Confirmation',
            'payment_proof_url' => 'payment-proofs/dummy-pending.jpg'
        ]);

        // Skenario 3: Pembalap Ditolak, daftar event akan datang
        EventRegistration::create([
            'event_id' => $eventAkanDatang->id,
            'pembalap_user_id' => $pembalapDitolak->id,
            'kis_category_id' => $catB5->id,
            'status' => 'Rejected',
            'payment_proof_url' => 'payment-proofs/dummy-ditolak.jpg',
            'admin_note' => 'Bukti transfer tidak jelas/buram. Harap upload ulang.',
            'payment_processed_at' => now(),
            'payment_processed_by_user_id' => $penyelenggara->id
        ]);
        
        $this->command->info('Full Story Seeder Selesai!');
    }
}