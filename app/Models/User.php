<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', 
        'club_id',
    ];

    protected $hidden = [ 'password', 'remember_token' ];
    protected $casts = [ 'email_verified_at' => 'datetime', 'password' => 'hashed' ];

    /**
     * Satu User (Penyelenggara) terhubung ke SATU Klub.
     */
    public function club()
    {
        // Relasi ini menggunakan 'club_id' di tabel 'users'
        return $this->belongsTo(Club::class);
    }
    // ==========================================================
    // ==                  AKHIR PERBAIKAN                   ==
    // ==========================================================


    // === RELASI ANDA YANG LAIN (Sudah Benar) ===
    
    // Gunakan alias 'profile' untuk konsistensi
    public function profile()
    {
        return $this->hasOne(RacerProfile::class);
    }

    // Alias untuk backward compatibility
    public function racerProfile()
    {
        return $this->profile();
    }

    public function kisApplications()
    {
        return $this->hasMany(KisApplication::class, 'pembalap_user_id');
    }

    public function kisLicense()
    {
        return $this->hasOne(KisLicense::class, 'pembalap_user_id');
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'pembalap_user_id');
    }

    public function processedKisApplications()
    {
        return $this->hasMany(KisApplication::class, 'processed_by_user_id');
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by_user_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'user_id');
    }

    public function processedDues()
    {
        return $this->hasMany(ClubDues::class, 'processed_by_user_id');
    }
}
