<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Sheet
 *
 * @property int $id
 * @property string $nomer номер листа
 * @property string $data дата листа
 * @property string $name наименование
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\SheetFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereNomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SheetDetail[] $sheet_details
 * @property-read int|null $sheet_details_count
 * @property-read \App\Models\User $user
 * @property int|null $user_id
 * @method static \Illuminate\Database\Eloquent\Builder|Sheet whereUserId($value)
 */
class Sheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nomer',
        'data',
        'user_id',
    ];

    public function sheet_details(): hasMany
    {
        return $this->hasMany(SheetDetail::class);
    }

    public function user(): belongsTo
    {
        return $this->belongsTo(User::class);
    }
}
