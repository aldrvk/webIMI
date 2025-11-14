<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_klub')->unique(); // Nama Klub (Contoh: SPEED'ER MOTORSPORT)
            $table->text('alamat')->nullable(); // Alamat Sekretariat
            $table->string('nama_ketua')->nullable(); // Nama Ketua
            $table->string('hp', 20)->nullable(); // HP Ketua
            $table->string('email_klub')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};