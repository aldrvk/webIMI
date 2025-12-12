<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Historical2024Seeder extends Seeder
{
    public function run()
    {
        $this->command->info('🚀 Memulai seeding data historical 2024...');
        
        // TAMBAHAN: Buat klub lebih banyak (15 klub)
        $this->command->info('🏢 Membuat 12 klub tambahan...');
        $additionalClubs = [
            ['nama_klub' => 'Racing Club Medan', 'alamat' => 'Jl. Gatot Subroto No. 123, Medan', 'nama_ketua' => 'Budi Santoso', 'hp' => '081234567893', 'email_klub' => 'racing.medan@example.com'],
            ['nama_klub' => 'Speedster Motorsport', 'alamat' => 'Jl. Sisingamangaraja No. 45, Medan', 'nama_ketua' => 'Andi Wijaya', 'hp' => '081234567894', 'email_klub' => 'speedster@example.com'],
            ['nama_klub' => 'Thunder Racing Team', 'alamat' => 'Jl. Iskandar Muda No. 78, Medan', 'nama_ketua' => 'Dedi Kurniawan', 'hp' => '081234567895', 'email_klub' => 'thunder@example.com'],
            ['nama_klub' => 'Velocity Club Sumut', 'alamat' => 'Jl. Dr. Mansyur No. 90, Medan', 'nama_ketua' => 'Eko Prasetyo', 'hp' => '081234567896', 'email_klub' => 'velocity@example.com'],
            ['nama_klub' => 'Nitro Racing Team', 'alamat' => 'Jl. Sudirman No. 112, Pematang Siantar', 'nama_ketua' => 'Faisal Rahman', 'hp' => '081234567897', 'email_klub' => 'nitro@example.com'],
            ['nama_klub' => 'Apex Motorsport', 'alamat' => 'Jl. Jend. Ahmad Yani No. 67, Binjai', 'nama_ketua' => 'Gunawan Tan', 'hp' => '081234567898', 'email_klub' => 'apex@example.com'],
            ['nama_klub' => 'Turbo Racing Club', 'alamat' => 'Jl. Kapten Maulana Lubis No. 34, Medan', 'nama_ketua' => 'Hendra Lim', 'hp' => '081234567899', 'email_klub' => 'turbo@example.com'],
            ['nama_klub' => 'Phoenix Motorsport', 'alamat' => 'Jl. Setia Budi No. 156, Medan', 'nama_ketua' => 'Indra Gunawan', 'hp' => '081234567800', 'email_klub' => 'phoenix@example.com'],
            ['nama_klub' => 'Dragon Racing Team', 'alamat' => 'Jl. Karya No. 89, Tebing Tinggi', 'nama_ketua' => 'Joko Widodo', 'hp' => '081234567801', 'email_klub' => 'dragon@example.com'],
            ['nama_klub' => 'Falcon Motorsport', 'alamat' => 'Jl. Veteran No. 45, Medan', 'nama_ketua' => 'Kurniawan Setiawan', 'hp' => '081234567802', 'email_klub' => 'falcon@example.com'],
            ['nama_klub' => 'Viper Racing Club', 'alamat' => 'Jl. Brigjend Katamso No. 78, Medan', 'nama_ketua' => 'Luhut Pangaribuan', 'hp' => '081234567803', 'email_klub' => 'viper@example.com'],
            ['nama_klub' => 'Eagle Motorsport Team', 'alamat' => 'Jl. Imam Bonjol No. 23, Medan', 'nama_ketua' => 'Mario Situmorang', 'hp' => '081234567804', 'email_klub' => 'eagle@example.com'],
        ];

        $newClubIds = [];
        foreach ($additionalClubs as $club) {
            $exists = DB::table('clubs')->where('nama_klub', $club['nama_klub'])->exists();
            if (!$exists) {
                $clubId = DB::table('clubs')->insertGetId([
                    'nama_klub' => $club['nama_klub'],
                    'alamat' => $club['alamat'],
                    'nama_ketua' => $club['nama_ketua'],
                    'hp' => $club['hp'],
                    'email_klub' => $club['email_klub'],
                    'created_at' => Carbon::create(2024, rand(1, 12), rand(1, 28)),
                    'updated_at' => now(),
                ]);
                $newClubIds[] = $clubId;
            }
        }
        $this->command->info("   ✓ Dibuat " . count($newClubIds) . " klub baru");

        // Ambil semua klub yang ada
        $existingClubs = DB::table('clubs')->pluck('id')->toArray();
        
        // Data Pembalap (100 pembalap untuk data lebih realistis)
        $pembalapIds = [];
        $this->command->info('📝 Membuat 100 pembalap dengan profil lengkap...');
        
        for ($i = 1; $i <= 100; $i++) {
            $month = rand(1, 12);
            $day = rand(1, 28);
            $clubId = $existingClubs[array_rand($existingClubs)];
            
            $pembalapIds[] = DB::table('users')->insertGetId([
                'name' => 'Pembalap Historical ' . $i,
                'email' => 'pembalap2024_' . $i . '@test.com',
                'password' => bcrypt('password'),
                'role' => 'pembalap',
                'created_at' => Carbon::create(2024, $month, $day),
                'updated_at' => Carbon::create(2024, rand(1, 12), rand(1, 28)),
            ]);
            
            // Insert profil pembalap langsung
            DB::table('pembalap_profiles')->insert([
                'user_id' => end($pembalapIds),
                'club_id' => $clubId,
                'tempat_lahir' => ['Medan', 'Jakarta', 'Bandung', 'Surabaya', 'Pematang Siantar'][array_rand(['Medan', 'Jakarta', 'Bandung', 'Surabaya', 'Pematang Siantar'])],
                'tanggal_lahir' => Carbon::create(rand(1985, 2005), rand(1, 12), rand(1, 28)),
                'no_ktp_sim' => '1271' . str_pad($i, 12, '0', STR_PAD_LEFT),
                'golongan_darah' => ['A', 'B', 'AB', 'O'][array_rand(['A', 'B', 'AB', 'O'])],
                'phone_number' => '0812345' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'address' => 'Jl. Test No. ' . $i . ', Medan',
                'created_at' => Carbon::create(2024, $month, $day),
                'updated_at' => now(),
            ]);
        }

        // Kategori KIS - TANPA timestamps
        $this->command->info('📋 Memeriksa kategori KIS...');
        $categories = [
            ['kode_kategori' => 'GP', 'nama_kategori' => 'Grand Prix', 'tipe' => 'Motor', 'biaya_kis' => 150000],
            ['kode_kategori' => 'SB', 'nama_kategori' => 'Supersport', 'tipe' => 'Motor', 'biaya_kis' => 150000],
            ['kode_kategori' => 'MP', 'nama_kategori' => 'Moped', 'tipe' => 'Motor', 'biaya_kis' => 150000],
        ];

        foreach ($categories as $cat) {
            $exists = DB::table('kis_categories')
                ->where('kode_kategori', $cat['kode_kategori'])
                ->exists();
                
            if (!$exists) {
                DB::table('kis_categories')->insert($cat);
            }
        }

        // TAMBAHAN: Iuran Klub 2024 - SEMUA KLUB
        $this->command->info('💰 Membuat data iuran klub tahun 2024...');
        
        foreach ($existingClubs as $clubId) {
            $isPaid = rand(1, 10) <= 8; // 80% klub bayar
            $paymentMonth = rand(1, 12);
            $paymentDay = rand(1, 28);
            $paymentDate = Carbon::create(2024, $paymentMonth, $paymentDay);
            
            DB::table('club_dues')->insert([
                'club_id' => $clubId,
                'payment_year' => 2024,
                'payment_date' => $paymentDate,
                'amount_paid' => 5000000,
                'payment_proof_url' => $isPaid ? 'payment-proofs/klub-' . $clubId . '-2024.jpg' : 'payment-proofs/pending-' . $clubId . '-2024.jpg',
                'status' => $isPaid ? 'Approved' : 'Pending',
                'processed_by_user_id' => $isPaid ? rand(1, 3) : null,
                'notes' => $isPaid ? 'Pembayaran iuran tahun 2024' : 'Menunggu verifikasi pembayaran',
                'created_at' => $paymentDate,
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info("   ✓ Dibuat " . count($existingClubs) . " data iuran klub (80% approved)");

        // Events sepanjang 2024 (60 events - lebih banyak untuk testing)
        $this->command->info('🏁 Membuat 60 events tahun 2024...');
        $eventNames = [
            // Q1 (Jan-Mar) - 15 events
            'New Year Cup 2024', 'Lunar Racing Championship', 'Independence Day Race',
            'Spring Championship Round 1', 'Spring Championship Round 2', 'Spring Championship Round 3',
            'Kejurda Medan Q1 Round 1', 'Kejurda Medan Q1 Round 2', 'Kejurda Medan Q1 Round 3',
            'Open Track Day January', 'Valentine Special Race', 'March Madness Race',
            'Pematang Siantar Cup 1', 'Binjai Racing Series 1', 'Tebing Tinggi Open 1',
            
            // Q2 (Apr-Jun) - 15 events
            'Summer Heat Championship R1', 'Summer Heat Championship R2', 'Summer Heat Championship R3',
            'Kejurprov Sumut Series 1', 'Kejurprov Sumut Series 2', 'Kejurprov Sumut Series 3',
            'Ramadan Cup 2024', 'Eid Mubarak Race', 'Independence Preparation Cup',
            'Mid Year Championship', 'Open Track Day April', 'May Day Racing',
            'Pematang Siantar Cup 2', 'Binjai Racing Series 2', 'June Thunder Race',
            
            // Q3 (Jul-Sep) - 15 events
            'Independence Day Special', 'August 17 Freedom Race', 'Merdeka Cup 2024',
            'Autumn Championship R1', 'Autumn Championship R2', 'Autumn Championship R3',
            'Grasstrack Medan Cup 1', 'Grasstrack Medan Cup 2', 'Grasstrack Medan Cup 3',
            'National Championship Q3 R1', 'Open Track Day July', 'September Speed Fest',
            'Tebing Tinggi Open 2', 'Binjai Racing Series 3', 'Sumut Grand Prix Round 1',
            
            // Q4 (Oct-Dec) - 15 events
            'Year End Championship R1', 'Year End Championship R2', 'Year End Championship R3',
            'Grand Final Preparation', 'Semi Final Championship', 'Grand Final Championship 2024',
            'Christmas Special Race', 'New Year Preparation Cup', 'Endurance Race 2024',
            'October Fast Race', 'November Thunder', 'December Speedway',
            'Pematang Siantar Cup 3', 'Sumut Grand Prix Round 2', 'IMI Sumut Closing Race'
        ];

        $eventIds = [];
        foreach ($eventNames as $index => $name) {
            $month = floor($index / 5) + 1;
            $month = min($month, 12);
            $day = rand(1, 28);
            
            $eventDate = Carbon::create(2024, $month, $day);
            $registrationDeadline = Carbon::create(2024, $month, max(1, $day - 3))->setTime(23, 59, 59);
            
            $eventIds[] = DB::table('events')->insertGetId([
                'event_name' => $name,
                'location' => ['Sirkuit Sentul', 'Sirkuit Medan', 'Sirkuit Deli Serdang', 'Sirkuit Pematang Siantar', 'Sirkuit Binjai'][array_rand(['Sirkuit Sentul', 'Sirkuit Medan', 'Sirkuit Deli Serdang', 'Sirkuit Pematang Siantar', 'Sirkuit Binjai'])],
                'event_date' => $eventDate,
                'registration_deadline' => $registrationDeadline,
                'description' => 'Event balap ' . $name . ' - Historical Data 2024',
                'biaya_pendaftaran' => rand(5, 20) * 100000,
                'bank_account_info' => 'BCA 1234567890 a/n IMI Sumut',
                'kontak_panitia' => '081234567890',
                'proposing_club_id' => $existingClubs[array_rand($existingClubs)],
                'created_by_user_id' => rand(1, 4),
                'is_published' => 1,
                'created_at' => Carbon::create(2024, max(1, $month - 1), rand(1, 28)),
                'updated_at' => now(),
            ]);
        }

        // KIS Applications - LEBIH BANYAK (200 applications)
        $this->command->info('📄 Membuat 200 KIS applications...');
        $statuses = ['Approved', 'Approved', 'Approved', 'Approved', 'Approved', 'Rejected', 'Pending'];
        $pembalapWithLicense = [];
        $totalKisRevenue = 0;
        
        // Ambil ID kategori yang valid
        $validCategoryIds = DB::table('kis_categories')->pluck('id')->toArray();
        
        for ($i = 0; $i < 200; $i++) {
            $pembalapId = $pembalapIds[array_rand($pembalapIds)];
            $categoryId = $validCategoryIds[array_rand($validCategoryIds)];
            $month = rand(1, 12);
            $day = rand(1, 28);
            $status = $statuses[array_rand($statuses)];
            
            $buktiBayarUrl = null;
            if ($status == 'Approved') {
                $buktiBayarUrl = 'kis-payments/bukti-bayar-' . $pembalapId . '-' . $i . '-2024.jpg';
                $totalKisRevenue += 150000;
            }
            
            $appId = DB::table('kis_applications')->insertGetId([
                'pembalap_user_id' => $pembalapId,
                'kis_category_id' => $categoryId,
                'status' => $status,
                'file_bukti_bayar_url' => $buktiBayarUrl,
                'file_surat_sehat_url' => $status == 'Approved' ? 'kis-docs/surat-sehat-' . $pembalapId . '.pdf' : null,
                'file_pas_foto_url' => $status == 'Approved' ? 'kis-docs/pas-foto-' . $pembalapId . '.jpg' : null,
                'file_ktp_url' => $status == 'Approved' ? 'kis-docs/ktp-' . $pembalapId . '.jpg' : null,
                'processed_by_user_id' => $status != 'Pending' ? rand(1, 3) : null,
                'rejection_reason' => $status == 'Rejected' ? 'Dokumen tidak lengkap' : null,
                'approved_at' => $status == 'Approved' ? Carbon::create(2024, $month, $day)->addDays(rand(1, 3)) : null,
                'created_at' => Carbon::create(2024, $month, $day),
                'updated_at' => Carbon::create(2024, $month, $day)->addDays(rand(1, 7)),
            ]);

            if ($status == 'Approved' && !in_array($pembalapId, $pembalapWithLicense)) {
                $categoryData = collect($categories)->firstWhere('kode_kategori', 
                    DB::table('kis_categories')->where('id', $categoryId)->value('kode_kategori')
                );
                
                $categoryCode = $categoryData ? $categoryData['kode_kategori'] : 'XX';
                $monthRoman = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$month - 1];
                $kisNumber = $appId . '/' . $categoryCode . '/MDN/' . $monthRoman . '/2024';
                
                $existingLicense = DB::table('kis_licenses')
                    ->where('pembalap_user_id', $pembalapId)
                    ->exists();
                
                if (!$existingLicense) {
                    DB::table('kis_licenses')->insert([
                        'pembalap_user_id' => $pembalapId,
                        'application_id' => $appId,
                        'kis_category_id' => $categoryId,
                        'kis_number' => $kisNumber,
                        'issued_date' => Carbon::create(2024, $month, $day),
                        'expiry_date' => Carbon::create(2024, 12, 31),
                        'created_at' => Carbon::create(2024, $month, $day),
                        'updated_at' => now(),
                    ]);
                    
                    $pembalapWithLicense[] = $pembalapId;
                }
            }
        }
        
        $this->command->info("   ✓ Total pendapatan dari KIS: Rp " . number_format($totalKisRevenue, 0, ',', '.'));

        // Event Registrations - LEBIH BANYAK (800+ registrations)
        $this->command->info('🏆 Membuat 800+ event registrations...');
        $positions = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, null, null, null, null];
        $pointsMap = [1 => 25, 2 => 20, 3 => 16, 4 => 13, 5 => 11, 6 => 10, 7 => 9, 8 => 8, 9 => 7, 10 => 6];
        $regStatuses = ['Confirmed', 'Confirmed', 'Confirmed', 'Confirmed', 'Pending Confirmation'];
        
        $totalRegistrations = 0;
        foreach ($eventIds as $eventId) {
            $participantsCount = rand(12, 20); // Lebih banyak peserta per event
            $selectedPembalap = array_slice($pembalapIds, 0, min($participantsCount, count($pembalapIds)));
            shuffle($selectedPembalap);
            
            foreach (array_slice($selectedPembalap, 0, $participantsCount) as $index => $pembalapId) {
                $position = $positions[array_rand($positions)];
                $points = $position && isset($pointsMap[$position]) ? $pointsMap[$position] : rand(0, 5);
                $regStatus = $regStatuses[array_rand($regStatuses)];
                $categoryId = $validCategoryIds[array_rand($validCategoryIds)];
                
                DB::table('event_registrations')->insert([
                    'event_id' => $eventId,
                    'pembalap_user_id' => $pembalapId,
                    'kis_category_id' => $categoryId,
                    'result_position' => $regStatus === 'Confirmed' ? $position : null,
                    'points_earned' => $regStatus === 'Confirmed' ? $points : 0,
                    'status' => $regStatus,
                    'payment_proof_url' => 'payment-proofs/dummy-2024-' . $pembalapId . '-' . $eventId . '.jpg',
                    'created_at' => Carbon::create(2024, rand(1, 12), rand(1, 28)),
                    'updated_at' => now(),
                ]);
                
                $totalRegistrations++;
            }
        }

        // Logs (500+ entries berbagai aktivitas sepanjang tahun)
        $this->command->info('📊 Membuat 500+ log entries...');
        $logActions = [
            ['action' => 'INSERT', 'table' => 'kis_applications', 'value' => 'Pengajuan KIS baru'],
            ['action' => 'UPDATE', 'table' => 'kis_applications', 'value' => 'Status diubah ke Approved'],
            ['action' => 'UPDATE', 'table' => 'kis_applications', 'value' => 'Status diubah ke Rejected'],
            ['action' => 'INSERT', 'table' => 'events', 'value' => 'Event baru dibuat'],
            ['action' => 'UPDATE', 'table' => 'events', 'value' => 'Event diupdate'],
            ['action' => 'INSERT', 'table' => 'event_registrations', 'value' => 'Peserta didaftarkan ke event'],
            ['action' => 'INSERT', 'table' => 'club_dues', 'value' => 'Iuran klub dibayar'],
        ];

        for ($i = 0; $i < 500; $i++) {
            $log = $logActions[array_rand($logActions)];
            $userId = rand(0, 2) == 0 ? rand(1, 4) : $pembalapIds[array_rand($pembalapIds)];
            $month = rand(1, 12);
            
            DB::table('logs')->insert([
                'action_type' => $log['action'],
                'table_name' => $log['table'],
                'record_id' => rand(1, 200),
                'old_value' => $log['action'] == 'UPDATE' ? 'Status lama: Pending' : null,
                'new_value' => $log['value'],
                'user_id' => $userId,
                'created_at' => Carbon::create(2024, $month, rand(1, 28), rand(0, 23), rand(0, 59)),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('');
        $this->command->info('✅ Historical data 2024 berhasil di-seed!');
        $this->command->info('');
        $this->command->info('📊 Summary Data yang Dibuat:');
        $this->command->info('   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('   🏢 ' . count($existingClubs) . ' Klub Total (termasuk ' . count($newClubIds) . ' klub baru)');
        $this->command->info('   👥 100 Pembalap Historical 2024 dengan profil lengkap');
        $this->command->info('   📋 ' . count($validCategoryIds) . ' Kategori KIS');
        $this->command->info('   💰 ' . count($existingClubs) . ' Data Iuran Klub (80% approved)');
        $this->command->info('   🏁 60 Events (spread di 12 bulan)');
        $this->command->info('   📄 200 KIS Applications (berbagai status)');
        $this->command->info('   💵 Revenue KIS: Rp ' . number_format($totalKisRevenue, 0, ',', '.'));
        $this->command->info('   💶 Revenue Iuran Klub: Rp ' . number_format(count($existingClubs) * 5000000, 0, ',', '.'));
        $this->command->info('   🏆 ' . $totalRegistrations . ' Event Registrations (dengan poin)');
        $this->command->info('   📊 500+ Log Entries (aktivitas sistem)');
        $this->command->info('   ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
        $this->command->info('🎯 Data siap untuk testcase dashboard pimpinan IMI!');
        $this->command->info('   - Filter tahun 2024 akan menampilkan semua data ini');
        $this->command->info('   - Filter "Overall" akan include data ini + data tahun lain');
        $this->command->info('   - Revenue metrics sudah include iuran klub & pendaftaran KIS');
        $this->command->info('   - Data klub, pembalap, dan events sangat lengkap untuk testing');
        $this->command->info('');
    }
}