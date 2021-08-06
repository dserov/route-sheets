<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\SheetDetail
 *
 * @property int $id
 * @property int $sheet_id
 * @property int $npp номер пп
 * @property string $contragent контрагент
 * @property string $playground площадка
 * @property string $overflow переполнение
 * @property string $note примечание
 * @property float $volume объем контрагента
 * @property int $count_plan количество план
 * @property float $count_units количество единицы
 * @property float $count_fact количество факт
 * @property float $count_general количество общии
 * @property string $mark отметка
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\SheetDetailFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereContragent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereCountFact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereCountGeneral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereCountPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereCountUnits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereMark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereNpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereOverflow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail wherePlayground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereSheetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SheetDetail whereVolume($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DetailFoto[] $detail_fotos
 * @property-read int|null $detail_fotos_count
 * @property-read SheetDetail $sheet
 */
class SheetDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'npp',
        'contragent',
        'playground',
        'overflow',
        'note',
        'volume',
        'count_plan',
        'count_units',
        'count_fact',
        'count_general',
        'mark',
    ];

    public function sheet(): belongsTo
    {
        return $this->belongsTo(Sheet::class);
    }

    public function detail_fotos(): hasMany
    {
        return $this->hasMany(DetailFoto::class);
    }
}
