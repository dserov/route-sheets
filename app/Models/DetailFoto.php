<?php

namespace App\Models;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto query()
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailFoto wherePath($value)
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
        'path',
    ];

    public function sheet_detail(): belongsTo
    {
        return $this->belongsTo(SheetDetail::class);
    }
}
