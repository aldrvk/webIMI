<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KisLicense extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relasi ke Pembalap (sudah ada)
    public function pembalap()
    {
        return $this->belongsTo(User::class, 'pembalap_user_id');
    }

    /**
     * Satu Lisensi KIS merujuk ke SATU Kategori KIS.
     */
    public function kisCategory()
    {
        return $this->belongsTo(KisCategory::class, 'kis_category_id');
    }
}