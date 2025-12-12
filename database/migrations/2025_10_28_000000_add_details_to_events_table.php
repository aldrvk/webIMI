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
    Schema::table('events', function (Blueprint $table) {
        // Kita tambahkan 3 kolom ini, TAPI 'daftar_kelas' dihilangkan
        $table->decimal('biaya_pendaftaran', 13, 2)->default(0.00)->after('description');
        $table->string('kontak_panitia')->nullable()->after('biaya_pendaftaran');
        $table->string('url_regulasi')->nullable()->after('kontak_panitia');
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::table('events', function (Blueprint $table) {
        // Logika kebalikan (hanya hapus 3 kolom)
        $table->dropColumn([
            'biaya_pendaftaran',
            'kontak_panitia',
            'url_regulasi'
        ]);
    });
}
};