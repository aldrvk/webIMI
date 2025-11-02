<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        $path = database_path('sql/triggers.sql');
        DB::unprepared(file_get_contents($path));
    }
    public function down(): void {
        // Drop triggers (opsional tapi baik)
        DB::unprepared('DROP TRIGGER IF EXISTS `auto_create_kis_license_on_approval`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_insert`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_kis_application_update`');
        DB::unprepared('DROP TRIGGER IF EXISTS `log_event_insert`');
    }
};