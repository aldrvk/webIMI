<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubDues extends Model
{
    use HasFactory;

    // Tentukan nama tabelnya secara eksplisit
    protected $table = 'club_dues';
    
    // Izinkan semua field diisi
    protected $guarded = [];

    /**
     * Satu catatan iuran dimiliki oleh SATU Klub.
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Satu catatan iuran diproses oleh SATU Pengurus (User).
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}