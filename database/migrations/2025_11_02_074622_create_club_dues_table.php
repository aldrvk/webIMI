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
        Schema::create('club_dues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->year('payment_year');
            $table->date('payment_date');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_proof_url'); // WAJIB, "Nota" Anda
            
            // Alur Kerja Persetujuan
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('rejection_reason')->nullable();
            
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users');
            
            $table->text('notes')->nullable(); 

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_dues');
    }
};