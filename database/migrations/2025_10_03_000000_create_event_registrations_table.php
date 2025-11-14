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
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            
            // Relasi
            $table->foreignId('event_id')->constrained('events');
            $table->foreignId('pembalap_user_id')->constrained('users');
            
            // PERBAIKAN: Mengganti 'category' (VARCHAR) dengan 'kis_category_id' (FOREIGN KEY)
            $table->foreignId('kis_category_id')->nullable()->constrained('kis_categories');
            
            // Data Hasil Lomba (dari form Penyelenggara)
            $table->integer('result_position')->nullable();
            $table->enum('result_status', ['Finished', 'DNF', 'DSQ'])->nullable();
            $table->integer('points_earned')->default(0);

            $table->enum('status', ['Pending Payment', 'Pending Confirmation', 'Confirmed', 'Rejected', 'Cancelled'])
                  ->default('Pending Payment');
            
            $table->string('payment_proof_url')->nullable();
            $table->text('admin_note')->nullable(); // Alasan penolakan
            
            $table->timestamp('payment_processed_at')->nullable();
            $table->foreignId('payment_processed_by_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};