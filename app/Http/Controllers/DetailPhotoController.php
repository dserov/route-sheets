<?php

namespace App\Http\Controllers;

use App\Models\DetailFoto;
use App\Models\SheetDetail;
use Illuminate\Http\Request;

class DetailPhotoController extends Controller
{
    public function listBySheetDetailId(SheetDetail $sheetDetail)
    {
        $this->authorize('view', $sheetDetail);
        $photos = $sheetDetail->detail_fotos()->paginate();
        return \View::make('detail_photo.index', [
            'sheet_detail' => $sheetDetail,
            'photos' => $photos,
        ]);
    }

    public function store(Request $request, SheetDetail $sheetDetail)
    {
        $this->authorize('view', $sheetDetail);
        $request->validate([
            'images' => 'required',
        ]);

        if ($request->hasfile('images')) {
            $images = $request->file('images');

            foreach ($images as $image) {
                $name = $image->hashName();
                $name = $this->_getUniqueFileName($name);
                $path = $image->storeAs('uploads', $name, 'public');

                DetailFoto::create([
                    'name' => $name,
                    'path' => '/storage/' . $path,
                    'sheet_detail_id' => $sheetDetail->id,
                ]);
            }
        }

        return back()->with('success', 'Images uploaded successfully');
    }

    private function _getUniqueFileName($name)
    {
        $folder = storage_path('uploads');
        $name = $this->_getNewName($name);
        while (is_file($folder . DIRECTORY_SEPARATOR . $name)) {
            $name = $this->_getNewName($name);
        }
        return $name;
    }

    private function _getNewName($name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $name = \Str::random(16) . ($ext ? "." . $ext : "");
        return \Str::lower($name);
    }
}
