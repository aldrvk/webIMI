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
        // Ini adalah tabel perantara (pivot)
        // Nama tabel adalah gabungan (singular) dari 2 model,
        // 'event' dan 'kis_category', diurutkan alfabetis.
        Schema::create('event_kis_category', function (Blueprint $table) {

            // 1. Foreign key ke tabel 'events'
            $table->foreignId('event_id')
                  ->constrained()
                  ->onDelete('cascade'); // Jika event dihapus, data ini ikut terhapus

            // 2. Foreign key ke tabel 'kis_categories'
            $table->foreignId('kis_category_id')
                  ->constrained()
                  ->onDelete('cascade'); // Jika kategori dihapus, data ini ikut terhapus

            // 3. (Best Practice) Jadikan kedua ID sebagai 'Primary Key'
            // Ini mencegah duplikasi (misal: event 1 terdaftar 2x di kategori C1)
            $table->primary(['event_id', 'kis_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_kis_category');
    }
};