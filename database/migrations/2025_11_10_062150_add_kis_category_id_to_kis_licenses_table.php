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
        Schema::table('kis_licenses', function (Blueprint $table) {
            // 1. Tambahkan kolomnya
            $table->unsignedBigInteger('kis_category_id')->nullable()->after('application_id');

            // 2. Tambahkan foreign key
            $table->foreign('kis_category_id')
                  ->references('id')
                  ->on('kis_categories')
                  ->onDelete('set null'); // Jika kategori dihapus, KIS tetap ada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kis_licenses', function (Blueprint $table) {
            // Hapus foreign key dulu (nama constraint: tabel_kolom_foreign)
            $table->dropForeign(['kis_category_id']);
            
            // Hapus kolomnya
            $table->dropColumn('kis_category_id');
        });
    }
};