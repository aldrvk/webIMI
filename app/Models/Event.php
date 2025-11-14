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
        'registration_deadline',
        'location',
        'description',
        'proposing_club_id',
        'created_by_user_id',
        'is_published',
        'biaya_pendaftaran',
        'kontak_panitia',
        'url_regulasi',
        'image_banner_url',
        'bank_account_info',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'biaya_pendaftaran' => 'float',
        'is_published' => 'boolean',
    ];

    public function proposingClub()
    {
        return $this->belongsTo(Club::class, 'proposing_club_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }

    public function kisCategories()
    {
        return $this->belongsToMany(KisCategory::class, 'event_kis_category');
    }
}