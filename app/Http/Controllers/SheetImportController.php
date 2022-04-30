<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRouteSheetRequest;
use App\Models\Sheet;
use Illuminate\Support\Facades\DB;
use Str;

class SheetImportController extends Controller
{
    protected $errors = [];
    protected $success = [];

    public function import(StoreRouteSheetRequest $request)
    {
        // ini_set('max_file_uploads', 100);
        $this->authorize('create', Sheet::class);
        if (false == $request->hasFile('route_sheets')) {
            return back()->withErrors(['Lists not founded']);
        }

        foreach ($request->file('route_sheets') as $file) {
            $tmp_file_name = $file->getRealPath();
            $result = $this->_importOne($tmp_file_name);
            if (is_string($result)) {
                $this->errors[] = $file->getClientOriginalName() . ': ' . $result;
            } elseif ($result === false) {
                $this->errors[] = $file->getClientOriginalName() . ': Загрузить не удалось';
            } else {
                $this->success[] = $file->getClientOriginalName() . ': Загружен успешно';
            }
        }

        if ($this->errors) {
            return back()->withErrors($this->errors)->with('status', implode('<br>', $this->success));
        }

        return response()->redirectToRoute('sheet::index')->with('status', implode('<br>', $this->success));
    }

    private function _importOne($tmp_file_name)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmp_file_name);
            $active_sheet = $spreadsheet->getActiveSheet()->toArray();

            $sheet = new Sheet();
            $sheetDetailsList = [];

            $is_header = true;
            $line_no = 1;
            if (count($active_sheet[0]) != 12 && count($active_sheet[0]) != 11) {
                return __('List format error!');
            }
            foreach ($active_sheet as $sheet_row) {
                if ($is_header) {
                    foreach ($sheet_row as $sheet_col) {
                        if (Str::of($sheet_col)->startsWith('Маршрутный лист № ')) {
                            // Маршрутный лист № Э0000078623 от 22 июня 2021 г.
                            $line = (string)Str::of($sheet_col)->replaceFirst('Маршрутный лист № ', '');
                            $result = $this->parseNomerData($line);
                            $sheet->nomer = $result['nomer'];
                            $sheet->data = $result['data'];
                            continue;
                        }
                        if (Str::of($sheet_col)->startsWith('Маршрут №: ')) {
                            $sheet->name = (string)Str::of($sheet_col)->replaceFirst('Маршрут №: ', '');
                            continue;
                        }
                        if ($sheet_col == '№') {
                            $is_header = false;
                        }
                    }
                } else {
                    // header is complete
                    if (is_null($sheet_row[0])) {
                        continue;
                    }

                    // fill line
                    if ($sheet_row[0] == $line_no++) {
                        $sheetDetailsList[] = [
                            'npp' => $sheet_row[0],
                            'contragent' => $sheet_row[1],
                            'playground' => $sheet_row[3],
                            //                        'overflow' => $sheet_row[4],
                            //                        'note' => $sheet_row[5],
                            //                        'volume' => $sheet_row[6],
                            //                        'count_plan' => $sheet_row[7],
                            //                        'count_units' => $sheet_row[8],
                            //                        'count_fact' => $sheet_row[9],
                            //                        'count_general' => $sheet_row[10],
                            //                        'mark' => $sheet_row[10],
                        ];
                    }
                }
            }
            $db_result = DB::transaction(function () use ($sheet, $sheetDetailsList) {
                $sheet->save();
                $sheet->sheet_details()->createMany($sheetDetailsList);
                return true;
            });
            return $db_result;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    private function parseNomerData($line)
    {
        $result = array(
            'nomer' => null,
            'data' => null,
        );
        $parts = explode(' от ', $line, 2);

        $result['nomer'] = $parts[0];
        $result['data'] = $parts[1];

        $parts = explode(' ', $result['data']);
        $month_list = array(
            'янв',
            'фев',
            'мар',
            'апр',
            'мая',
            'июн',
            'июл',
            'авг',
            'сен',
            'окт',
            'ноя',
            'дек',
        );

        $month_list = array_flip($month_list);

        $parts[1] = (string)Str::of($parts[1])->substr(0, 3);
        $parts[1] = $month_list[$parts[1]] + 1;

        $result['data'] = sprintf('%04d-%02d-%02d', $parts[2], $parts[1], $parts[0]);

        return $result;
    }

    public function showImportForm()
    {
        $this->authorize('create', Sheet::class);
        return \View::make('sheet.import');
    }
}
