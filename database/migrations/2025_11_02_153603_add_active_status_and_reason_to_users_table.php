<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom boolean untuk status aktif (default: aktif/true)
            $table->boolean('is_active')->default(true)->after('role'); 
            
            // Kolom string untuk menyimpan alasan non-aktif (nullable/opsional)
            $table->string('deactivation_reason')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Urutan drop harus sesuai dengan urutan add
            $table->dropColumn('deactivation_reason');
            $table->dropColumn('is_active');
        });
    }
};
