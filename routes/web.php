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

Route::redirect('/', '/sheet');

Auth::routes();

Route::group([
    'prefix' => 'sheet',
    'as' => 'sheet::',
    'middleware' => ['auth'],
], function (){
    Route::get('/', [App\Http\Controllers\SheetController::class, 'index'])->name('index');
    Route::get('/search', [App\Http\Controllers\SheetController::class, 'search'])->name('search');

    Route::get('/xls', [App\Http\Controllers\SheetImportController::class, 'showImportForm'])->name('import_form');
    Route::post('/xls', [App\Http\Controllers\SheetImportController::class, 'import'])->name('import_save');

    // set driver id
    Route::get('/{sheet}', [App\Http\Controllers\SheetController::class, 'update'])->name('update');
    Route::put('/{sheet}', [App\Http\Controllers\SheetController::class, 'store'])->name('store');

    // delete sheet
    Route::delete('/{sheet}', [App\Http\Controllers\SheetController::class, 'delete'])->name('delete');

    // show content sheet
    Route::get('/{sheet}/sheet_detail', [App\Http\Controllers\SheetDetailController::class, 'show'])->name('sheet_detail');
});

Route::group([
    'prefix' => 'map',
    'as' => 'map::',
    'middleware' => ['auth'],
], function (){
    Route::get('/', [App\Http\Controllers\MapController::class, 'index'])->name('index');
    Route::get('/kml', [\App\Http\Controllers\MapController::class, 'showImportForm'])->name('import_form');
    Route::post('/kml', [App\Http\Controllers\MapController::class, 'import'])->name('import_save');
});

Route::group([
    'prefix' => 'sheetdetail',
    'as' => 'sheet_detail::',
    'middleware' => ['auth'],
], function () {

    Route::get('/{sheetDetail}/detail_photo', [App\Http\Controllers\DetailPhotoController::class, 'listBySheetDetailId'])->name('detail_photo::list_by_sheet_detail');
    Route::post('/{sheetDetail}/detail_photo', [App\Http\Controllers\DetailPhotoController::class, 'store'])->name('detail_photo::upload_photos');
});

Route::group([
    'prefix' => 'admin/profile',
    'as' => 'admin::profile::',
    'middleware' => ['auth'],
], function () {
    Route::get('/', [AdminProfileController::class, 'index'])->name('index');
    Route::get('/import', [AdminProfileController::class, 'showImportForm'])->name('import_form');
    Route::post('/import', [AdminProfileController::class, 'import'])->name('import');
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
