<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'event_name',
        'event_date',
        'location',
        'description',
        'proposing_club_id',
        'created_by_user_id',
        'is_published',
    
        'biaya_pendaftaran',
        'kontak_panitia',
        'url_regulasi',
        'daftar_kelas',
    ];

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

    /**
     * Relasi MANY-TO-MANY:
     * Satu Event bisa memiliki BANYAK Kategori KIS (kelas).
     */
    public function kisCategories()
    {
        return $this->belongsToMany(KisCategory::class, 'event_kis_category');
    }
}