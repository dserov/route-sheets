<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGeoListRequest;
use App\Models\GeoPoint;
use App\Models\Ut;
use PharIo\Manifest\ElementCollectionException;

class MapController extends Controller
{
    const PREFIX = 'УТ';

    private $_errors = [];

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
        $string = preg_replace('/[\x00-\x1F]/', '', $string);
        return (string) \Str::of($string)->trim();
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

        try {
            // парсинг и загрузка kml-файла.
            $dataGeopoint = [];
            if ($request->hasFile('geo_list')) {
                $file = $request->file('geo_list');
                $fileName = $file->getPathname();
                $dataGeopoint = $this->_parseGeopointKml($fileName);
                $this->_insertIntoTableGeoPoint($dataGeopoint);
            } else {
                $dataGeopoint = GeoPoint::all()->toArray();
            }

            if (empty($dataGeopoint)) {
                throw new \Exception('Сначала необходимо загрузить kml-файл');
            }

            // convert $dataGeopoint
            $dataGeopointDictionary = $this->_convertDataGeopointToDictionary($dataGeopoint);

            if ($request->hasFile('ut_list')) {
                $file = $request->file('ut_list');
                $fileName = $file->getPathname();
                $dataUtList = $this->_parseUtXls($fileName, $dataGeopointDictionary);
                $this->_insertIntoTableUts($dataUtList);
            }

            return response()->redirectToRoute('map::index')->with('status', __('Files was loaded succesfully'));
        } catch (\Exception $exception) {
            $this->_errors[] = $exception->getMessage();
            return back()->withErrors($this->_errors);
        } catch (\Throwable $exception) {
            $this->_errors[] = $exception->getMessage();
            return back()->withErrors($this->_errors);
        }
    }

    /**
     * @param array $dataGeopoint
     * @return array
     */
    private function _convertDataGeopointToDictionary($dataGeopoint) {
        $data = [];
        foreach ($dataGeopoint as $item) {
            $result = \Str::of($item['description'])->matchAll('/(\d{7})/');
            foreach ($result as $part) {
                $key = self::PREFIX . $part;
                $data[$key][] = $item['id'];
            }
        }
        return $data;
    }

    /**
     * @param $dataGeopoint
     * @throws \Throwable
     */
    private function _insertIntoTableGeoPoint($dataGeopoint)
    {
        // insert data
        $chunks = array_chunk($dataGeopoint, 500);
        $dbResult = \DB::transaction(function () use ($chunks) {
            \DB::table('geo_points')->delete();

            $isNotErrorInsert = true;
            foreach ($chunks as $chunk) {
                $isNotErrorInsert = $isNotErrorInsert && GeoPoint::insert($chunk);
            }

            return $isNotErrorInsert;
        });
        if ($dbResult === false) {
            throw new \Exception('Error inserting data into table geo_points.');
        }
    }

    /**
     * @param $dataUtList
     * @throws \Throwable
     */
    private function _insertIntoTableUts($dataUtList)
    {
        // insert data
        $chunks = array_chunk($dataUtList, 500);
        $dbResult = \DB::transaction(function () use ($chunks) {
            \DB::table('uts')->delete();

            $isNotErrorInsert = true;
            foreach ($chunks as $chunk) {
                $isNotErrorInsert = $isNotErrorInsert && Ut::insert($chunk);
            }

            return $isNotErrorInsert;
        });
        if ($dbResult === false) {
            throw new \Exception('Error inserting data into table uts.');
        }
    }

    /**
     * @param $fullFileName
     * @return array
     * @throws \Exception
     */
    private function _parseGeopointKml($fullFileName)
    {
        $data = [];
        $xml = simplexml_load_file($fullFileName);
        $placemarks = $xml->xpath('Document/Placemark');
        $invalidEntry = [];
        $id = 1;
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

            // строки без номера договора игнорируем
            if (\Str::contains($description, self::PREFIX)) {
                $data[] = [
                    'id' => $id++,
                    'name' => $name,
                    'description' => $description,
                    'point' => serialize(array_merge($point, $polygon)),
                ];
            }
        }

        if (!empty($invalidEntry)) {
            $this->_errors[] = $invalidEntry;
        }

        if (empty($data)) {
            throw new \Exception('Данные из kml-файла не удалось загрузить');
        }

        return $data;
    }

    /**
     * @param string $fullFileName
     * @param array $dataGeopointDictionary
     * @return array|null|string
     */
    private function _parseUtXls($fullFileName, $dataGeopointDictionary)
    {
        try {
            $utList = [];
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullFileName);
            $workSheet = $spreadsheet->setActiveSheetIndexByName('жилье+юр.лица');

            $active_sheet = $workSheet->toArray();

            if (count($active_sheet[0]) < 8) {
                throw new \Exception(__('List format error!'));
            }

            $is_header = true;
            foreach ($active_sheet as $sheet_row) {
                if ($is_header) {
                    // skip header
                    if ($sheet_row[0] == 'N') {
                        $is_header = false;
                    }
                    continue;
                } else {
                    // fill line
                    $ut_number = (string) \Str::of($sheet_row[4])->trim();
                    if (!array_key_exists($ut_number, $dataGeopointDictionary)) {
                        $this->_errors[] = 'Договор ' . $ut_number . ' не найден в kml-файле';
                        continue;
                    }
                    $geo_point_list = $dataGeopointDictionary[$ut_number];
                    foreach ($geo_point_list as $geo_point_id) {
                            $utList[] = [
                                'geo_point_id' => $geo_point_id,
                                'playground' => (string)\Str::of($sheet_row[1])->trim(),
                                'container_type' => (string)\Str::of($sheet_row[2])->trim(),
                                'container_volume' => (string)\Str::of($sheet_row[3])->trim(),
                                'ut_number' => $ut_number,
                                'export_schedule' => (string)\Str::of($sheet_row[5])->trim(),
                                'export_days' => (string)\Str::of($sheet_row[6])->trim(),
                                'export_volume' => (string)\Str::of($sheet_row[7])->trim(),
                        ];
                    }
                }
            }
            return $utList;
        } catch (\Exception $exception) {
            $this->_errors[] = $exception->getMessage();
            return [];
        }
    }

    /**
     * @param $ut_number
     * @param $dataGeopoint
     *
     * @return array
     */
    private function _findGeoPointId($ut_number, $dataGeopoint)
    {
        $filteredGeoPoint = array_filter($dataGeopoint, function ($item) use ($ut_number) {
            return \Str::contains($item['description'], $ut_number);
        });

        return \Arr::pluck($filteredGeoPoint, 'id');
    }
}
