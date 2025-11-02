<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;
    protected $guarded = []; // Izinkan mass assignment

    // Satu Klub memiliki BANYAK profil pembalap
    public function pembalapProfiles()
    {
        return $this->hasMany(PembalapProfile::class);
    }

    // Satu Klub mengajukan BANYAK event
    public function proposedEvents()
    {
        return $this->hasMany(Event::class, 'proposing_club_id');
    }
    
    /**
     * Satu Klub memiliki BANYAK riwayat iuran.
     * (Relasi ke tabel 'club_dues')
     */
    public function duesHistory()
    {
        return $this->hasMany(ClubDues::class, 'club_id')->orderBy('payment_year', 'desc');
    }
}
