<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kis_categories', function (Blueprint $table) {
            $table->decimal('biaya_kis', 10, 2)->default(150000)->after('nama_kategori');
        });
        
        // Update data existing
        DB::statement("UPDATE kis_categories SET biaya_kis = 200000 WHERE tipe = 'Mobil'");
        DB::statement("UPDATE kis_categories SET biaya_kis = 150000 WHERE tipe = 'Motor'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kis_categories', function (Blueprint $table) {
            $table->dropColumn('biaya_kis');
        });
    }
};
