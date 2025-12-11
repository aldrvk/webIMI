<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->nullable()->after('status');
        });
        
        DB::statement("
            UPDATE event_registrations er
            JOIN events e ON er.event_id = e.id
            SET er.amount_paid = e.biaya_pendaftaran
            WHERE er.status = 'Confirmed'
        ");
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }
};
