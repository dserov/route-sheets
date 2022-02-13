<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailPhotoRequest;
use App\Models\DetailFoto;
use App\Models\SheetDetail;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Exception\NotFoundException;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DetailPhotoController extends Controller
{
    const IMAGE_DIR = 'images';
    const THUMB_DIR = 'thumbnails';

    public function listBySheetDetailId(SheetDetail $sheetDetail)
    {
        $this->authorize('view', $sheetDetail);
        $photos = $sheetDetail->detail_fotos()->paginate();
        return \View::make('detail_photo.index', [
            'sheet_detail' => $sheetDetail,
            'photos' => $photos,
        ]);
    }

    public function delete($sheetDetail, $detailFoto) {
      header('Content-type: application/json');
      $response =array(
        'message' => 'Что-то пошло не по плану.',
        'error' => 'true',
      );
      try {
        $foto = DetailFoto::find($detailFoto);
        if ($foto === null) throw new NotFoundHttpException('Foto not found');
        $this->authorize('delete', $foto);

        $basePath = \Storage::disk('public')->path('');
        $filePath = $basePath . self::IMAGE_DIR . DIRECTORY_SEPARATOR . $foto->name;
        $thumbPath = $basePath . self::THUMB_DIR . DIRECTORY_SEPARATOR . $foto->name;
        try {
          \File::delete($filePath);
        } catch (\Exception $e) {}
        try {
          \File::delete($thumbPath);
        } catch (\Exception $e) {}
        try {
          $foto->delete();
        } catch (\Exception $e) {}
        $response['error'] = 'false';
        $response['message'] = 'Фотография удалена';
      } catch (NotFoundHttpException $notFoundException) {
        $response['message'] = 'Фотография не найдена';
      } catch (AuthorizationException $authorizationException) {
        $response['message'] = 'У вас нет прав делать это';
      } catch (\Exception $exception) {
      }
      return json_encode($response);
    }

    public function rotate($sheetDetail, $detailFoto) {
      $response =array(
        'message' => 'Что-то пошло не по плану.',
      );
      try {
        $foto = DetailFoto::find($detailFoto);
        if ($foto === null) throw new NotFoundHttpException('Foto not found');
        $this->authorize('delete', $foto);

        $basePath = \Storage::disk('public')->path('');
        $filePath = $basePath . self::IMAGE_DIR . DIRECTORY_SEPARATOR . $foto->name;
        $thumbPath = $basePath . self::THUMB_DIR . DIRECTORY_SEPARATOR . $foto->name;

        // save fullimage
        $img = Image::make($thumbPath);
        $img->rotate(270)->save($thumbPath);

        // save fullimage
        $img = Image::make($filePath);
        $img->rotate(270)->save($filePath);

        $response['message'] = '';
      } catch (NotFoundHttpException $notFoundHttpException) {
        $response['message'] = $notFoundHttpException->getMessage();
      } catch (\Exception $exception) {}

      return json_encode($response);
    }

    public function store(DetailPhotoRequest $request, SheetDetail $sheetDetail)
    {
        if ($request->hasfile('images')) {
            $images = $request->file('images');

            foreach ($images as $image) {
                $name = $this->_saveFiles($image);
                if (is_string($name)) {
                    $data = [
                        'name' => $name,
                        'path' => '/storage/' . self::IMAGE_DIR . '/' . $name,
                        'thumb' => '/storage/' . self::THUMB_DIR . '/' . $name,
                        'sheet_detail_id' => $sheetDetail->id,
                    ];

                    DetailFoto::create($data);
                } else {
                    return back()->withErrors('Foto not uploaded');
                }
            }
        }

        return back()->with('success', 'Images uploaded successfully');
    }

    /**
     * @param UploadedFile $image
     * @return string|bool
     */
    private function _saveFiles($image)
    {
        $filePath = null;
        $thumbPath = null;
        try {
            $name = $image->hashName();
            $name = $this->_getUniqueFileName($name);

            $basePath = \Storage::disk('public')->path('');
            $filePath = $basePath . self::IMAGE_DIR . DIRECTORY_SEPARATOR . $name;
            $thumbPath = $basePath . self::THUMB_DIR . DIRECTORY_SEPARATOR . $name;

            // save fullimage
            $img = Image::make($image->path());
            $img->resize(1024, 1024, function ($const) {
                $const->aspectRatio();
            })->save($filePath);

            // save thumbnail
            $img = Image::make($image->path());
            $img->resize(300, 300, function ($const) {
                $const->aspectRatio();
            })->save($thumbPath);

            // check if images is exists
            if (\File::exists($filePath) && \File::exists($thumbPath)) {
                return $name;
            }
            throw new \Exception('');
        } catch (\Exception $e) {
            if ($filePath && \File::exists($filePath)) \File::delete($filePath);
            if ($thumbPath && \File::exists($thumbPath)) \File::delete($thumbPath);
            return false;
        }
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
