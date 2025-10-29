<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistration extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Satu pendaftaran event dimiliki oleh SATU Pembalap
    public function pembalap()
    {
        return $this->belongsTo(User::class, 'pembalap_user_id');
    }

    // Satu pendaftaran event merujuk ke SATU Event
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
