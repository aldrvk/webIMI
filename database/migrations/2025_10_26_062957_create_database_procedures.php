<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $path = database_path('sql/procedures.sql');
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_RegisterPembalapToEvent`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_GetLeaderboard`');
        DB::unprepared(file_get_contents($path));
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_RegisterPembalapToEvent`');
        DB::unprepared('DROP PROCEDURE IF EXISTS `Proc_GetLeaderboard`');
    }
};
