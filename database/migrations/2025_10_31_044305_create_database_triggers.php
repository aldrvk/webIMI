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
        $sql = file_get_contents(database_path('sql/triggers.sql'));

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
        // Drop all triggers in reverse order
        DB::unprepared('DROP TRIGGER IF EXISTS `log_club_dues_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_club_dues_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_registration_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_registration_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `auto_create_kis_license_on_approval`');
    }
};