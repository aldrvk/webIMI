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
        Schema::table('event_registrations', function (Blueprint $table) {
            // 1. Hapus kolom 'category' (VARCHAR) yang salah
            $table->dropColumn('category');
            
            // 2. Tambahkan 'kis_category_id' (BIGINT) yang benar
            //    Kita letakkan setelah 'pembalap_user_id'
            $table->unsignedBigInteger('kis_category_id')->nullable()->after('pembalap_user_id');

            // 3. (PENTING) Tambahkan foreign key untuk integritas data
            $table->foreign('kis_category_id')
                  ->references('id')
                  ->on('kis_categories')
                  ->onDelete('set null'); // Jika kategori dihapus, pendaftaran tidak ikut terhapus
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // Logika kebalikan jika migrasi di-rollback
            $table->dropForeign(['kis_category_id']);
            $table->dropColumn('kis_category_id');
            $table->string('category', 100)->after('pembalap_user_id'); // Kembalikan kolom 'category'
        });
    }
};  