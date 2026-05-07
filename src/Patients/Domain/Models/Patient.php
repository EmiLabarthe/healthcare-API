<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                     $id
 * @property string                  $name
 * @property string                  $email
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Patient extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /** @return Attribute<string, string> */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value): string => strtolower((string) $value),
            set: static fn (mixed $value): string => strtolower((string) $value),
        );
    }
}
