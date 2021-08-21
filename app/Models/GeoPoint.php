<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'point',
    ];

    public function getAsJson()
    {
        $points = self::all();
        $data = [];
        foreach ($points as $point) {
            $data[] = [
                'name' => $point->name,
                'description' => $point->description,
                'geometry' => $this->_makeGeoObject($point->point),
            ];
        }

        return $data;
    }

    private function _makeGeoObject($pointList)
    {
        $pointList = unserialize($pointList);
        if (!is_array($pointList)) {
            return false;
        }

        if (is_array($pointList[0])) {
            return [
                'type' => 'Polygon',
                'coordinates' => $pointList,
            ];
        }
        return [
            'type' => 'Point',
            'coordinates' => $pointList,
        ];
    }
}
