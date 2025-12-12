<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kis_categories', function (Blueprint $table) {
            $table->decimal('biaya_kis', 10, 2)->default(0)->after('tipe');
        });
    }

    public function down(): void
    {
        Schema::table('kis_categories', function (Blueprint $table) {
            $table->dropColumn('biaya_kis');
        });
    }
};