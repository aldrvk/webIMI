<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Satu Event dibuat oleh SATU Pengurus
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Satu Event memiliki BANYAK pendaftaran
    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }
}
