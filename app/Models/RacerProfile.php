<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RacerProfile extends Model
{
    use HasFactory;

    protected $table = 'pembalap_profiles';

    protected $fillable = [
        'user_id',
        'club_id',
        'tanggal_lahir',
        'nomor_telepon',
        'alamat',
        'jenis_kelamin',
        'ukuran_baju',
        'golongan_darah',
        'kontak_darurat',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function kisLicense()
    {
        return $this->hasOne(KisLicense::class, 'pembalap_user_id', 'user_id');
    }
}
