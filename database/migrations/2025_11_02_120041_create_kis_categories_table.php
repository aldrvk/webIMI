<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('kis_categories', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kategori', 10)->unique(); // Contoh: "C1", "C2", "B1"
            $table->string('nama_kategori'); // Contoh: "Motocross, Supercross, Grasstrack"
            $table->enum('tipe', ['Mobil', 'Motor']); // Berdasarkan form
        });
    }
    public function down(): void {
        Schema::dropIfExists('kis_categories');
    }
};
