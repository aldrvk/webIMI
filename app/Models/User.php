<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', 
        'phone_number', 
        'address', 
        'is_active', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // === RELASI UNTUK PEMBALAP ===

    // Satu Pembalap memiliki BANYAK pengajuan KIS
    public function kisApplications()
    {
        return $this->hasMany(KisApplication::class, 'pembalap_user_id');
    }

    // Satu Pembalap memiliki SATU lisensi KIS (yang aktif)
    public function kisLicense()
    {
        return $this->hasOne(KisLicense::class, 'pembalap_user_id');
    }

    // Satu Pembalap memiliki BANYAK pendaftaran event (CV-nya)
    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class, 'pembalap_user_id');
    }

    // === RELASI UNTUK PENGURUS IMI ===

    // Satu Pengurus telah memproses BANYAK pengajuan KIS
    public function processedKisApplications()
    {
        return $this->hasMany(KisApplication::class, 'processed_by_user_id');
    }

    // Satu Pengurus telah membuat BANYAK event
    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by_user_id');
    }

    // === RELASI UMUM ===
    
    // Satu User (bisa siapa saja) telah membuat BANYAK log
    public function logs()
    {
        return $this->hasMany(Log::class, 'user_id');
    }
}
