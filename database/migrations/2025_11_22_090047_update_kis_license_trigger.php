<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Memperbaiki trigger auto_create_kis_license_on_approval untuk menangani LAST_INSERT_ID() dengan benar
     */
    public function up(): void
    {
        // Baca file trigger yang sudah diperbaiki
        $sql = file_get_contents(database_path('sql/triggers.sql'));
        
        // Pecah berdasarkan delimiter '$$'
        $statements = array_filter(array_map('trim', explode('$$', $sql)));
        
        // Jalankan hanya trigger yang diperlukan (trigger pertama)
        foreach ($statements as $statement) {
            if (!empty($statement) && strpos($statement, 'auto_create_kis_license_on_approval') !== false) {
                // Hapus trigger lama dulu
                DB::unprepared('DROP TRIGGER IF EXISTS `auto_create_kis_license_on_approval`');
                
                // Jalankan trigger baru
                DB::unprepared($statement);
                break; // Hanya jalankan trigger pertama yang relevan
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `auto_create_kis_license_on_approval`');
    }
};
