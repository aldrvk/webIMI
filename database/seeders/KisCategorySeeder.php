<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\KisCategory;

class KisCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Data Kategori KIS Motor
        KisCategory::create(['kode_kategori' => 'C1', 'nama_kategori' => 'Balap Motor, Dragsbike', 'tipe' => 'Motor']);
        KisCategory::create(['kode_kategori' => 'C2', 'nama_kategori' => 'Motocross, Supercross, Grasstrack', 'tipe' => 'Motor']);
        KisCategory::create(['kode_kategori' => 'C3', 'nama_kategori' => 'Rally', 'tipe' => 'Motor']);

        // Data Kategori KIS Mobil
        KisCategory::create(['kode_kategori' => 'A1', 'nama_kategori' => 'Racing, Drag Race', 'tipe' => 'Mobil']);
        KisCategory::create(['kode_kategori' => 'B1', 'nama_kategori' => 'Rally/Sprint', 'tipe' => 'Mobil']);
        KisCategory::create(['kode_kategori' => 'B3', 'nama_kategori' => 'Offroad Adventure/Sprint', 'tipe' => 'Mobil']);
        KisCategory::create(['kode_kategori' => 'B4', 'nama_kategori' => 'Drift', 'tipe' => 'Mobil']);
        KisCategory::create(['kode_kategori' => 'B5', 'nama_kategori' => 'Karting', 'tipe' => 'Mobil']);
        KisCategory::create(['kode_kategori' => 'B6', 'nama_kategori' => 'Slalom', 'tipe' => 'Mobil']);
    }
}