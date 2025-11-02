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
        Schema::create('pembalap_profiles', function (Blueprint $table) {
            $table->id();

            // Relasi One-to-One ke tabel 'users'. 
            // onDelete('cascade') berarti jika user dihapus, profilnya juga ikut terhapus.
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade'); 

            // Relasi ke tabel 'clubs'. Sesuai aturan bisnis, ini tidak boleh null (NOT NULL).
            $table->foreignId('club_id')->constrained('clubs'); 

            // Data spesifik pembalap dari temuan formulir
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('no_ktp_sim')->nullable()->unique(); // Sebaiknya unik
            $table->enum('golongan_darah', ['A', 'B', 'AB', 'O', '-'])->nullable(); //

            // Kolom profil yang kita pindahkan dari tabel 'users'
            $table->string('phone_number', 20)->nullable(); 
            $table->text('address')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembalap_profiles');
    }
};