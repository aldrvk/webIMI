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
        Schema::create('kis_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembalap_user_id')->constrained('users');
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->string('file_surat_sehat_url')->nullable();
            $table->string('file_bukti_bayar_url')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kis_applications');
    }
};
