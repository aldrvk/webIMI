<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Baca isi file SQL
        $sql = file_get_contents(database_path('sql/functions_views.sql'));

        // 2. Pecah berdasarkan delimiter '$$' (sama seperti procedure)
        $statements = array_filter(array_map('trim', explode('$$', $sql)));

        // 3. Jalankan setiap statement
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                // JANGAN tambahkan ';'
                DB::unprepared($statement);
            }
        }

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