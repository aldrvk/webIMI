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
        Schema::table('kis_applications', function (Blueprint $table) {
            // Tambahkan 2 kolom baru setelah 'kis_category_id'
            $table->string('file_ktp_url')->nullable()->after('kis_category_id');
            $table->string('file_pas_foto_url')->nullable()->after('file_ktp_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kis_applications', function (Blueprint $table) {
            $table->dropColumn(['file_ktp_url', 'file_pas_foto_url']);
        });
    }
};