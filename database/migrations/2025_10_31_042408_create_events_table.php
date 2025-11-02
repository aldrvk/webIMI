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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->date('event_date');
            $table->string('location');
            $table->text('description')->nullable();
            
            // Siapa Klub yang menyelenggarakan/mengajukan
            $table->foreignId('proposing_club_id')->constrained('clubs');

            // Siapa Pengurus IMI yang mempublikasikan data ini
            $table->foreignId('created_by_user_id')->constrained('users');
            
            // Boolean untuk 'Tampilkan/Sembunyikan' dari Pembalap
            $table->boolean('is_published')->default(true); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};