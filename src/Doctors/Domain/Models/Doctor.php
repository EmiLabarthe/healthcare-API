<?php

declare(strict_types=1);

namespace Lightit\Doctors\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lightit\Clinics\Domain\Models\Clinic;
use Lightit\Clinics\Domain\Models\ClinicDoctor;

/**
 * @property int                     $id
 * @property string                  $name
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property-read ClinicDoctor|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Clinic> $clinics
 * @property-read int|null $clinics_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Doctor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /** @return BelongsToMany<Clinic, $this> */
    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class)
            ->using(ClinicDoctor::class)
            ->withTimestamps();
    }
}
