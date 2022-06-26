<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'description',
        'point',
        'radius',
    ];

    public function uts()
    {
        return $this->hasMany(Ut::class);
    }

    public function getAsJson()
    {
        $points = self::all();
//        $points = self::query()->with(['uts'])->get();
        $data = [];
        foreach ($points as $point) {
            $data[] = [
                'id' => $point->id,
                'name' => $point->name,
                'description' => $point->description,
                'geometry' => self::makeGeoObject($point->point, $point->radius),
//                'uts' => json_encode($point->uts)
            ];
        }

        return $data;
    }

    static public function makeGeoObject($pointList, $radius)
    {
        $pointList = unserialize($pointList);
        if (!is_array($pointList)) {
            return false;
        }

        if (is_array($pointList[0])) {
            return [
                'type' => 'Polygon',
                'coordinates' => [
                    $pointList
                ],
            ];
        }

        if ($radius > 0) {
          return [
              'type' => 'Circle',
              'coordinates' => $pointList,
              'radius' => $radius,
          ];
        }

        return [
            'type' => 'Point',
            'coordinates' => $pointList,
        ];
    }
}
