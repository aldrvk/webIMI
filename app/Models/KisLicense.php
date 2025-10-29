<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KisLicense extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Satu Lisensi KIS dimiliki oleh SATU Pembalap
    public function pembalap()
    {
        return $this->belongsTo(User::class, 'pembalap_user_id');
    }
}
