<?php

namespace App\Http\Controllers;

use App\Models\Sheet;
use App\Models\SheetDetail;
use Illuminate\Http\Request;

class SheetDetailController extends Controller
{
    public function show(Sheet $sheet)
    {
        $this->authorize('view', $sheet);

        $sheet_details = $sheet->sheet_details()->with(['detail_fotos'])->get();

        $sheet_details_sorted = $sheet_details->sortBy(function($item) {
            return $item->foto_count;
        });
        return \View::make('sheet_detail.index', [
            'sheet' => $sheet,
            'sheet_details' => $sheet_details_sorted,
        ]);
    }
}
