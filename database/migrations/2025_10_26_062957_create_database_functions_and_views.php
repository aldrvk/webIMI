<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $path = database_path('sql/functions_views.sql');
        DB::unprepared('DROP FUNCTION IF EXISTS `Func_GetPembalapTotalPoints`');
        DB::unprepared('DROP VIEW IF EXISTS `View_Leaderboard`');
        DB::unprepared(file_get_contents($path));
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS `Func_GetPembalapTotalPoints`');
        DB::unprepared('DROP VIEW IF EXISTS `View_Leaderboard`');
    }
};
