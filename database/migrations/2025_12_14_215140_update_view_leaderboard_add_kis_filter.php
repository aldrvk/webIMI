<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration untuk meng-update View_Leaderboard agar konsisten dengan Proc_GetLeaderboard
 * Perubahan:
 * - Menambahkan filter KIS aktif (kis_licenses dengan expiry_date >= CURDATE())
 * - Menambahkan kolom nama_klub
 * - Menambahkan filter points_earned > 0
 */
class UpdateViewLeaderboardAddKisFilter extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop view lama
        DB::unprepared('DROP VIEW IF EXISTS `View_Leaderboard`');
        
        // Buat view baru yang sudah diharmonisasi dengan Proc_GetLeaderboard
        DB::unprepared("
            CREATE VIEW `View_Leaderboard` AS
            SELECT 
                u.name AS nama_pembalap, 
                c.nama_klub,
                kc.nama_kategori AS kategori, 
                kc.id AS kategori_id,
                SUM(er.points_earned) AS total_poin, 
                COUNT(er.id) AS jumlah_balapan
            FROM event_registrations AS er
            JOIN users AS u ON er.pembalap_user_id = u.id
            JOIN pembalap_profiles AS pp ON u.id = pp.user_id
            JOIN clubs AS c ON pp.club_id = c.id
            JOIN kis_licenses AS kl ON u.id = kl.pembalap_user_id AND kl.expiry_date >= CURDATE()
            LEFT JOIN kis_categories AS kc ON er.kis_category_id = kc.id
            WHERE er.points_earned > 0
            GROUP BY er.pembalap_user_id, u.name, c.nama_klub, kc.nama_kategori, kc.id
            ORDER BY total_poin DESC
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke versi lama (tanpa filter KIS)
        DB::unprepared('DROP VIEW IF EXISTS `View_Leaderboard`');
        
        DB::unprepared("
            CREATE VIEW `View_Leaderboard` AS
            SELECT 
                u.name AS nama_pembalap, kc.nama_kategori AS kategori, kc.id AS kategori_id,
                SUM(er.points_earned) AS total_poin, COUNT(er.id) AS jumlah_balapan
            FROM event_registrations AS er
            JOIN users AS u ON er.pembalap_user_id = u.id
            LEFT JOIN kis_categories AS kc ON er.kis_category_id = kc.id
            GROUP BY er.pembalap_user_id, u.name, kc.nama_kategori, kc.id
            ORDER BY kategori ASC, total_poin DESC
        ");
    }
}
