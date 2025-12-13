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
        $sql = file_get_contents(database_path('sql/procedures.sql'));

        // 2. Pecah berdasarkan delimiter '$$'
        $statements = array_filter(array_map('trim', explode('$$', $sql)));

        // 3. Jalankan setiap statement
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
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_Admin_RecordDues`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_ApplyForKIS`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_GetLeaderboard`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_RegisterPembalap`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_RegisterPembalapToEvent`');
    }
};