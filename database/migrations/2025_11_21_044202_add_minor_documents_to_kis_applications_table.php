<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kis_applications', function (Blueprint $table) {
            $table->string('file_kk_url')->nullable()->after('file_pas_foto_url');
            $table->string('file_surat_izin_url')->nullable()->after('file_kk_url');

            $table->string('file_ktp_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kis_applications', function (Blueprint $table) {
            $table->dropColumn(['file_kk_url', 'file_surat_izin_url']);
        });
    }
};