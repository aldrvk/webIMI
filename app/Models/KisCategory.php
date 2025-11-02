<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KisCategory extends Model
{
    use HasFactory;

    protected $table = 'kis_categories';

    protected $guarded = [];
    public $timestamps = false;
    public function kisApplications()
    {
        return $this->hasMany(KisApplication::class, 'kis_category_id');
    }
}