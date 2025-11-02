<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * (Hanya berisi kolom di tabel 'users')
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Pastikan 'role' ada di sini
    ];

    protected $hidden = [ 'password', 'remember_token' ];
    protected $casts = [ 'email_verified_at' => 'datetime', 'password' => 'hashed' ];

    // === RELASI BARU ===
    // Satu User (pembalap) memiliki SATU Profil Pembalap
    public function profile()
    {
        return $this->hasOne(PembalapProfile::class);
    }

    // === RELASI LAMA (MASIH VALID) ===
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

    public function createdEvents() // Ganti nama dari approvedEvents
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
