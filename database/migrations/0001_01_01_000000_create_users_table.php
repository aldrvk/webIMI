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
        // HANYA MEMBUAT TABEL 'users'
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Kolom Kustom
            $table->enum('role', [
                'pembalap',
                'pengurus_imi',
                'pimpinan_imi',
                'penyelenggara_event',
                'super_admin'
            ])->default('pembalap');
            $table->string('phone_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // HANYA MENGHAPUS TABEL 'users'
        Schema::dropIfExists('users');
    }
};