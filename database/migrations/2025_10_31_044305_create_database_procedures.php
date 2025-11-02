<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        $path = database_path('sql/procedures.sql');
        DB::unprepared(file_get_contents($path));
    }
    public function down(): void {
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_RegisterPembalapToEvent`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_GetLeaderboard`');
    }
};