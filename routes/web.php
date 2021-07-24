<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Admin\ProfileController as AdminProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/sheets');

Route::get('/w', function () {
    $publicPath = storage_path('app\public');

    $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($publicPath . "/1.xls");
    $dataArray = $spreadSheet->getActiveSheet()
        ->rangeToArray(
            'A1:K150',     // The worksheet range that we want to retrieve
            NULL,        // Value that should be returned for empty cells
            TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
            TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
            TRUE         // Should the array be indexed by cell row and cell column
        );
    dd($dataArray);

    return view('welcome');
});

Auth::routes();

Route::get('/sheets', [App\Http\Controllers\SheetController::class, 'index'])->name('sheets::index');
Route::get('/sheets/load', [App\Http\Controllers\SheetController::class, 'loadNew'])->name('sheets::load');
Route::get('/sheetdetail/{sheet}', [App\Http\Controllers\SheetDetailController::class, 'show'])->name('sheet_detail::show');

Route::get('/sheetdetail/{sheetDetail}/detail_photo', [App\Http\Controllers\DetailPhotoController::class, 'listBySheetDetailId'])->name('sheet_detail::detail_photo::list_by_sheet_detail');
Route::post('/sheetdetail/{sheetDetail}/detail_photo', [App\Http\Controllers\DetailPhotoController::class, 'store'])->name('sheet_detail::detail_photo::upload_photos');

Route::group([
    'prefix' => '/admin/profile',
    'as' => 'admin::profile::',
    'middleware' => ['auth'],
], function () {
    Route::get('/', [AdminProfileController::class, 'index'])->name('index');
    Route::get('/import', [AdminProfileController::class, 'import'])->name('import');
    Route::get('/create', [AdminProfileController::class, 'create'])->name('create');
    Route::get('/update/{user}', [AdminProfileController::class, 'update'])->name('update');
    Route::get('/delete/{user}', [AdminProfileController::class, 'destroy'])->name('delete');
    Route::post('/save', [AdminProfileController::class, 'save'])->name('save');
});

Route::get('/storage/{file}', function ($fileName) {
    if (! \Storage::disk('public')->has($fileName)) {
        abort(400);
    }
    return \Storage::disk('public')->response($fileName);
})->where('file', '.*');
