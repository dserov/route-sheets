<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExportController extends Controller
{
  function __construct()
  {
    $this->middleware(['auth']);
  }

  public function index(Request $request)
  {
    return \View::make('admin.export.index');
  }

  public function export(Request $request)
  {
    dd('export');
  }
}
