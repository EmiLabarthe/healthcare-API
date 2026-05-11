<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lightit\Doctors\Domain\Models\Doctor;

/**
 * @property int                     $id
 * @property string                  $name
 * @property string                  $address
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property-read \Lightit\Clinics\Domain\Models\ClinicDoctor|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Doctor> $doctors
 * @property-read int|null $doctors_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clinic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clinic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clinic query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clinic whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clinic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clinic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clinic whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Clinic whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Clinic extends Model
{
    #[\Override]
    protected $guarded = ['id'];

    /** @return BelongsToMany<Doctor, $this, ClinicDoctor> */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class)
            ->using(ClinicDoctor::class)
            ->withTimestamps();
    }
}
