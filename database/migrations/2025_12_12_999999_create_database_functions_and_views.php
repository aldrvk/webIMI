<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // CEK DEPENDENCIES SEBELUM LOAD SQL
        $requiredTables = [
            'club_dues',           // ← HARUS ADA!
            'kis_categories',
            'kis_applications',
            'event_registrations',
            'events',
            'kis_licenses'
        ];
        
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                // Skip migration jika dependency belum ada
                \Log::warning("Migration skipped: Table '{$table}' does not exist yet.");
                return;
            }
        }
        
        // Jika semua tabel sudah ada, baru load SQL
        $sql = file_get_contents(database_path('sql/functions_views.sql'));
        $statements = array_filter(array_map('trim', explode('$$', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                DB::unprepared($statement);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS `Func_GetPembalapTotalPoints`');
        DB::unprepared('DROP VIEW IF EXISTS `View_Leaderboard`');
        DB::unprepared('DROP VIEW IF EXISTS `View_Finished_Events`');
        DB::unprepared('DROP VIEW IF EXISTS `View_Detailed_Event_Results`');
        DB::unprepared('DROP VIEW IF EXISTS `View_Dashboard_KPIs`');
    }
};