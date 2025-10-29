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
        Schema::create('kis_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembalap_user_id')->unique()->constrained('users');
            $table->foreignId('application_id')->constrained('kis_applications');
            $table->string('kis_number', 100)->unique();
            $table->date('issued_date');
            $table->date('expiry_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kis_licenses');
    }
};
