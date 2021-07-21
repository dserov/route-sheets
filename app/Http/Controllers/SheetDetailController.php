<?php

namespace App\Http\Controllers;

use App\Models\Sheet;
use Illuminate\Http\Request;

class SheetDetailController extends Controller
{
    public function show(Sheet $sheet)
    {
        $sheet_details = $sheet->sheet_details()->with(['detail_fotos'])->paginate(10);

        return \View::make('sheet_detail.index', [
            'sheet' => $sheet,
            'sheet_details' => $sheet_details,
        ]);
    }
}
