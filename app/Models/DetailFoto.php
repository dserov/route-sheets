<?php

namespace App\Models;

use App\Http\Controllers\DetailPhotoController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\DetailFoto
 *
 * @property int $id
 * @property int $sheet_detail_id код строки маршрутного листа
 * @property string $name имя файла
 * @property string $path относительный путь
 * @property int $rotate поворот картинки
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto query()
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereRotate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereSheetDetailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\SheetDetail $sheet_detail
 * @property string|null $description описание
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereDescription($value)
 */
class DetailFoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'sheet_detail_id',
        'name',
        'thumb',
        'path',
        'rotate',
    ];

    public function sheet_detail(): belongsTo
    {
        return $this->belongsTo(SheetDetail::class);
    }

    public static function boot()
    {
        parent::boot();

        DetailFoto::deleted(function ($model){
            self::delete_foto($model);
        });
    }

    private static function delete_foto($detail_photo) {
      $basePath = \Storage::disk('public')->path('');
      $filePath = $basePath . DetailPhotoController::IMAGE_DIR . DIRECTORY_SEPARATOR . $detail_photo->name;
      $thumbPath = $basePath . DetailPhotoController::THUMB_DIR . DIRECTORY_SEPARATOR . $detail_photo->name;
      try {
        \File::delete($filePath);
      } catch (\Exception $e) {}
      try {
        \File::delete($thumbPath);
      } catch (\Exception $e) {}
    }
}
