<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembalapProfile extends Model
{
    use HasFactory;
    
    protected $table = 'pembalap_profiles'; 
    protected $guarded = []; 

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}