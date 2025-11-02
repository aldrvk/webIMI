<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        $path = database_path('sql/functions_views.sql');
        DB::unprepared(file_get_contents($path));
    }
    public function down(): void {
        DB::unprepared('DROP FUNCTION IF EXISTS `Func_GetPembalapTotalPoints`');
        DB::unprepared('DROP VIEW IF EXISTS `View_Leaderboard`');
    }
};