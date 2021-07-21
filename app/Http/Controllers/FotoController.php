<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FotoController extends Controller
{
    //
    public function store(Request $request)
    {
        // save enclosure
        if($uploadedFile = $request->file('news.enclosure')) {
            $fileName = $uploadedFile->store('', 'public');
            $news['enclosure'] = \Storage::url($fileName);
        }
    }
}
