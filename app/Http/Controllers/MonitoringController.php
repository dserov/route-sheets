<?php

namespace App\Http\Controllers;

use App\Models\GeoPoint;
use App\Models\Ut;
use Illuminate\Http\Request;

// Радиус земли
define('EARTH_RADIUS', 6372795);

class MonitoringController extends Controller
{
  //
  public function index()
  {
    $this->authorize('create', GeoPoint::class);
    $geoRows = (new GeoPoint)->getAsJson();

    return \View::make('monitoring.index', [
      'geo_list' => json_encode($geoRows),
    ]);
  }

  public function balloon($geoZoneId)
  {
    $rows = Ut::query()->where('geo_point_id', $geoZoneId)->get();
    return response()->json($rows);
  }

  public function geo()
  {
    $this->authorize('create', GeoPoint::class);
    $points = GeoPoint::all();
    $data = [
      'type' => 'FeatureCollection',
      'features' => [],
    ];
    foreach ($points as $point) {
      $geoObject = GeoPoint::makeGeoObject($point->point, $point->radius);
      // geozone
      $d = [
        'type' => 'Feature',
        'id' => 'z-' . $point->id,
        'geometry' => $geoObject,
        'properties' => [
//          'iconCaption' => $point->description,
//          'iconContent' => $point->description,
          'hintContent' => $point->name,
          'gi' => $point->id,
          'gt' => 'geoZone',
          'gn' => $point->name,
//          'gd' => $point->description,
        ],
        'options' => [],
      ];

      // transform to point type
      if ($geoObject['type'] == 'Polygon') {
        $geoObject['coordinates'] = $geoObject['coordinates'][0][0];
      }
      $geoObject['type'] = 'Point';
      unset($geoObject['radius']);

      //label for geozone
      $data['features'][] = [
        'type' => 'Feature',
        'id' => 'l-' . $point->id,
        'geometry' => $geoObject,
        'properties' => [
//          'iconCaption' => $point->description,
          'iconContent' => $point->description,
//          'hintContent' => $point->name,
//          'gi' => $point->id,
//          'gt' => 'geoZoneLabel',
//          'gn' => $point->name,
//          'gd' => $point->description,
        ],
        'options' => [
          'preset' => 'islands#darkBlueStretchyIcon',
        ],
      ];
    }

    return response()->json($data);
  }

  // получение геозон по координатам центра круга и его радиуса

  /**
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Auth\Access\AuthorizationException
   */
  public function geoDistance(Request $request)
  {
    $this->authorize('create', GeoPoint::class);
    if (empty($request->input('lat')) || empty($request->input('lng')) || empty($request->input('radius'))) {
      return response()->json([]);
    }

    $latCenter = $request->input('lat');
    $lngCenter = $request->input('lng');
    $radius = $request->input('radius');

    $points = GeoPoint::all();
    $data = [
      'type' => 'FeatureCollection',
      'features' => [],
    ];
    foreach ($points as $point) {
      $geoPoint = $geoObject = GeoPoint::makeGeoObject($point->point, $point->radius);
      // transform to point type
      if ($geoPoint['type'] == 'Polygon') {
        $geoPoint['coordinates'] = $geoPoint['coordinates'][0][0];
      }
      $geoPoint['type'] = 'Point';
      unset($geoPoint['radius']);

      // check distance
      if ($this->calculateTheDistance($latCenter, $lngCenter, $geoPoint['coordinates'][0], $geoPoint['coordinates'][1]) > $radius) {
        continue;
      }

      // geozone
      $data['features'][] = [
        'type' => 'Feature',
        'id' => 'z-' . $point->id,
        'geometry' => $geoObject,
        'properties' => [
//          'iconCaption' => $point->description,
//          'iconContent' => $point->description,
          'hintContent' => $point->name,
          'gi' => $point->id,
          'gt' => 'zone',
          'gn' => $point->name,
//          'gd' => $point->description,
        ],
        'options' => [],
      ];

      //label for geozone
      $data['features'][] = [
        'type' => 'Feature',
        'id' => 'l-' . $point->id,
        'geometry' => $geoPoint,
        'properties' => [
//          'iconCaption' => $point->description,
          'iconContent' => $point->description,
//          'hintContent' => $point->name,
          'gi' => $point->id,
          'gt' => 'label',
          'gn' => $point->name,
//          'gd' => $point->description,
        ],
        'options' => [
          'preset' => 'islands#darkBlueStretchyIcon',
        ],
      ];
    }

    return response()->json($data);
  }

  /*
  * Расстояние между двумя точками
  * $φA, $λA - широта, долгота 1-й точки,
  * $φB, $λB - широта, долгота 2-й точки
  * Написано по мотивам http://gis-lab.info/qa/great-circles.html
  * Михаил Кобзарев <mikhail@kobzarev.com>
  *
  */
  public function calculateTheDistance($φA, $λA, $φB, $λB)
  {
// перевести координаты в радианы
    $lat1 = $φA * M_PI / 180;
    $lat2 = $φB * M_PI / 180;
    $long1 = $λA * M_PI / 180;
    $long2 = $λB * M_PI / 180;

// косинусы и синусы широт и разницы долгот
    $cl1 = cos($lat1);
    $cl2 = cos($lat2);
    $sl1 = sin($lat1);
    $sl2 = sin($lat2);
    $delta = $long2 - $long1;
    $cdelta = cos($delta);
    $sdelta = sin($delta);

// вычисления длины большого круга
    $y = sqrt(pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2));
    $x = $sl1 * $sl2 + $cl1 * $cl2 * $cdelta;

//
    $ad = atan2($y, $x);
    $dist = $ad * EARTH_RADIUS;

    return $dist;
  }

  public function test()
  {
    $text = '{
    "type": "FeatureCollection",
    "features": [
        {"type": "Feature", "id": 0, "geometry": {"type": "Point", "coordinates": [55.831903, 37.411961], "radius": 50}, "properties": {"clusterCaption": "Еще одна метка", "hintContent": "Текст подсказки"}},
        {"type": "Feature", "id": 1, "geometry": {"type": "Point", "coordinates": [55.763338, 37.565466], "radius": 50}, "properties": {"clusterCaption": "Еще одна метка", "hintContent": "Текст подсказки"}}
    ]
}';
    return response()->json(json_decode($text));
  }
}
