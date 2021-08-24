<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ut extends Model
{
    use HasFactory;

    public function geo_point()
    {
        return $this->belongsTo(GeoPoint::class);
    }
}
