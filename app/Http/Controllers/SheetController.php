<?php

namespace App\Http\Controllers;

use App\Models\Sheet;
use Illuminate\Http\Request;

class SheetController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $sheets = Sheet::paginate(10);
        return view('sheet.index', [
            'sheets' => $sheets,
        ]);
    }

    public function loadNew()
    {
        return 'Loading...';
    }
}
