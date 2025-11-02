<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    
    protected $guarded = []; // Izinkan mass assignment

    /**
     * SATU Event diajukan oleh SATU Klub.
     * (Relasi ke tabel 'clubs')
     */
    public function proposingClub()
    {
        return $this->belongsTo(Club::class, 'proposing_club_id');
    }

    /**
     * SATU Event dipublikasikan oleh SATU Pengurus.
     * (Relasi ke tabel 'users')
     */
    public function creator() // Menggunakan nama relasi 'creator'
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * SATU Event memiliki BANYAK pendaftaran.
     * (Relasi ke 'event_registrations')
     */
    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }
}