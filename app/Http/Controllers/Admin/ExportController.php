<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DetailPhotoController;
use App\Http\Requests\ExportRequest;
use App\Models\DetailFoto;
use App\Models\Sheet;
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

  public function export(ExportRequest $request)
  {
    try {
      $dateFrom = \DateTime::createFromFormat('d/m/Y', $request->input('from_date'));
      $dateTo = \DateTime::createFromFormat('d/m/Y', $request->input('to_date'));

      $rows = Sheet::whereBetween('data', [$dateFrom->format('Y/m/d'), $dateTo->format('Y/m/d')])
        ->with(['sheet_details', 'sheet_details.detail_fotos'])->get();

      $basePath = \Storage::disk('public')->path('');
      $exportPath = $basePath . 'export';
      $exportPath = str_replace('/', '\\', $exportPath);
      $exportPath = str_replace('\\', DIRECTORY_SEPARATOR, $exportPath);

      // delete existing directory
      if (\File::isDirectory($exportPath)) {
        \File::deleteDirectory($exportPath);
      }

      // make temp export directory
      \File::ensureDirectoryExists($exportPath);

      chdir($exportPath);

      foreach ($rows as $row) {
        foreach ($row->sheet_details as $sheet_detail) {
          $contragentName = $this->validateFilename($sheet_detail->contragent);
          $playgroundName = $this->validateFilename($sheet_detail->playground);

          // copy fotos
          foreach ($sheet_detail->detail_fotos as $detail_foto) {
            $fileFullName = $basePath . DetailPhotoController::IMAGE_DIR . DIRECTORY_SEPARATOR . $detail_foto->name;
            if (\File::exists($fileFullName)) {
              $fotoPath = $exportPath . DIRECTORY_SEPARATOR . $row->data . DIRECTORY_SEPARATOR . $contragentName . DIRECTORY_SEPARATOR . $playgroundName . DIRECTORY_SEPARATOR;
              \File::ensureDirectoryExists($fotoPath);
              \File::copy($fileFullName, $fotoPath . $detail_foto->name);
              usleep(1000);
            }
          }
        }
      }

      $files = \File::allFiles($exportPath);
      if (count($files) == 0) {
        // files not found
        return redirect()
          ->route('admin::export::index')
          ->with('status', 'Файлы для выгрузки за указанный период не найдены!');
      }

      // make zip archive
      $zip = new \ZipArchive();
      $zipFileName = sprintf('export_%s-%s.zip', $dateFrom->format('Y.m.d'), $dateTo->format('Y.m.d'));
      if ($zip->open( $zipFileName, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)== TRUE)
      {
        $zip->addGlob('*/*/*/*', 0, [
          'remove_path' => $exportPath
        ]);
        $zip->close();
      }

      if (\File::exists($exportPath . DIRECTORY_SEPARATOR . $zipFileName)) {
        return response()->download($exportPath . DIRECTORY_SEPARATOR . $zipFileName);
      }

      return redirect()
        ->route('admin::export::index')
        ->with('status', 'Что-то пошло не так. Файлы есть, а заархивировать не удалось!');
    } catch (\Exception $exception) {
      return redirect()->route('admin::export::index')
        ->withErrors([$exception->getMessage()])
        ->withInput();
    }
  }

  private function validateFilename(string $filename)
  {
    $filename = \Str::of($filename)->trim();
    $invalidCharacters = ['|', '\'', '\\', '?', '*', '&', '<', '"', ';', ':', '>', '+', '[', ']', '=', '/'];
    return str_replace($invalidCharacters, '_', $filename);
  }

  // Function to recursively add a directory,
  // sub-directories and files to a zip archive
  /**
   * @param $dir
   * @param \ZipArchive $zipArchive
   * @param string $zipdir
   */
  private function addFolderToZip($dir, $zipArchive, $zipdir = ''){
    dump('$dir = ' . $dir);
    if (is_dir($dir)) {
      if ($dh = opendir($dir)) {
        //Add the directory
        if(!empty($zipdir)) $zipArchive->addEmptyDir($zipdir);

        // Loop through all the files
        while (($file = readdir($dh)) !== false) {

          //If it's a folder, run the function again!
          if(!is_file($dir . $file)){
            // Skip parent and root directories
            if( ($file !== ".") && ($file !== "..")){
              $this->addFolderToZip($dir . $file . DIRECTORY_SEPARATOR, $zipArchive, $zipdir . $file . DIRECTORY_SEPARATOR);
            }
          }else{
            // Add the files
            $zipArchive->addFile($dir . $file, $zipdir . $file);
          }
        }
      }
    }
  }
}
