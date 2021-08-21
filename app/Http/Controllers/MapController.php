<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGeoListRequest;
use App\Models\GeoPoint;

class MapController extends Controller
{
    public function index()
    {
        $this->authorize('create', GeoPoint::class);
        $geoRows = (new GeoPoint)->getAsJson();

        return \View::make('map.index', [
            'geo_list' => json_encode($geoRows),
        ]);
    }

    public function showImportForm()
    {
        $this->authorize('create', GeoPoint::class);
        return \View::make('map.import');
    }

    /**
     * @param \SimpleXMLElement $nodes []
     * @return int|string
     */
    private function getSingleValue($nodes)
    {
        if ($nodes === false || empty($nodes)) {
            return '';
        }

        $string = ((string)\Str::of((string)$nodes[0])->trim());
        return preg_replace('/[\x00-\x1F]/', '', $string);
    }

    /**
     * Преобразует строку координат в массив, или многомерный массив
     *
     * @param $text
     * @return array|null
     */
    private function _convertStringCoordinatesToArray($text)
    {
        if (empty($text)) {
            return [];
        };
        $parts = explode(' ', $text);
        $data = [];
        foreach ($parts as $part) {
            list($a, $b,) = explode(',', $part);
            $data[] = [doubleval($b), doubleval($a)];
        }
        if (count($data) == 1) {
            return current($data);
        }
        return $data;
    }

    public function import(StoreGeoListRequest $request)
    {
        $this->authorize('create', GeoPoint::class);
        if (false == $request->hasFile('geo_list')) {
            return back()->withErrors(['Geo lists not founded']);
        }

        $file = $request->file('geo_list');

        // $file->getPathname()
        $fileName = $file->getPathname(); //"D:\Downloads\Geofences (1).kml";

        $data = [];

        $xml = simplexml_load_file($fileName);
        $placemarks = $xml->xpath('Document/Placemark');
        $invalidEntry = [];
        foreach ($placemarks as $placemark) {
            $name = $this->getSingleValue($placemark->xpath('name'));
            $description = $this->getSingleValue($placemark->xpath('description'));
            $point = $this->_convertStringCoordinatesToArray($this->getSingleValue($placemark->xpath('Point/coordinates')));
            $polygon = $this->_convertStringCoordinatesToArray($this->getSingleValue($placemark->xpath('Polygon/outerBoundaryIs/LinearRing/coordinates')));
            if (empty($polygon) && empty($point)) {
                \Log::debug('Entry is invalid' . $name);
                $invalidEntry[] = 'Entry is invalid' . $name;
                continue;
            }
            $data[] = [
                'name' => $name,
                'description' => $description,
                'point' => serialize(array_merge($point, $polygon)),
            ];
        }

        if (!empty($invalidEntry)) {
            return back()->withErrors($invalidEntry);
        }

        $chunks = array_chunk($data, 500);
        $isNotErrorInsert = true;
        \DB::transaction(function () use ($chunks, &$isNotErrorInsert) {
            \DB::table('geo_points')->delete();

            foreach ($chunks as $chunk) {
                $isNotErrorInsert = $isNotErrorInsert && GeoPoint::insert($chunk);
            }
        });
        if (!$isNotErrorInsert) {
            return back()->withErrors('Error inserting data into table.');
        }

        return response()->redirectToRoute('map::index')->with('status', __('File was loaded succesfully'));
    }
}
