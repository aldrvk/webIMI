<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KisApplication extends Model
{
    use HasFactory;

    protected $guarded = []; // Cara cepat mengizinkan semua mass assignment

    // Satu Pengajuan KIS dimiliki oleh SATU Pembalap
    public function pembalap()
    {
        return $this->belongsTo(User::class, 'pembalap_user_id');
    }

    // Satu Pengajuan KIS diproses oleh SATU Pengurus
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function category()
    {
        return $this->belongsTo(KisCategory::class, 'kis_category_id'); // <-- Perbaikan!
    }
}
