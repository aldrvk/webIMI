<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Club; // <-- Import Model Club

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        // Buat 3 klub contoh berdasarkan data lapangan
        Club::create([
            'nama_klub' => 'IMI Sumut Official', 
            'alamat' => 'Jl. Taruma No. 52 Medan',
            'nama_ketua' => 'Harun Nasution',
            'hp' => '081234567890', // Data dummy
            'email_klub' => 'admin@imi-sumut.or.id' // Data dummy
        ]);
        
        Club::create([
            'nama_klub' => 'SPEED\'ER MOTORSPORT', //
            'alamat' => 'Jl. Jorlang Hatoran No. 85 A, Siantar',
            'nama_ketua' => 'Hasanuddin Lubis',
            'hp' => '081234567891', // Data dummy
            'email_klub' => 'speeder@example.com' // Data dummy
        ]);
        
        Club::create([
            'nama_klub' => 'Kitakita Motorsport', //
            'alamat' => 'Medan',
            'nama_ketua' => 'Adek Hidayat',
            'hp' => '081234567892', // Data dummy
            'email_klub' => 'kitakita@example.com' // Data dummy
        ]);
    }
}