<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Rekening Pembayaran KIS
        Setting::updateOrCreate(
            ['key' => 'kis_bank_account'],
            [
                'value' => "Bank BCA\nNo. Rek: 888-123-4567\nA/n IMI Sumatera Utara",
                'description' => 'Informasi rekening bank untuk pembayaran pendaftaran KIS.'
            ]
        );

        // 2. Biaya Pendaftaran KIS (Default)
        Setting::updateOrCreate(
            ['key' => 'kis_registration_fee'],
            [
                'value' => '150000',
                'description' => 'Biaya pendaftaran pembuatan KIS (dalam Rupiah).'
            ]
        );
    }
}