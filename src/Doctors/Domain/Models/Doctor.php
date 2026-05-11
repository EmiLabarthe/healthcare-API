<?php

declare(strict_types=1);

namespace Lightit\Doctors\Domain\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lightit\Appointments\Domain\Models\Appointment;
use Lightit\Clinics\Domain\Models\Clinic;
use Lightit\Clinics\Domain\Models\ClinicDoctor;

/**
 * @property int             $id
 * @property string          $name
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property-read ClinicDoctor|null $pivot
 * @property-read Collection<int, Clinic> $clinics
 * @property-read int|null $clinics_count
 * @property-read Collection<int, Appointment> $appointments
 * @property-read int|null $appointments_count
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
    #[\Override]
    protected $guarded = ['id'];

    /** @return BelongsToMany<Clinic, $this, ClinicDoctor> */
    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class)
            ->using(ClinicDoctor::class)
            ->withTimestamps();
    }

    /** @return HasMany<Appointment, $this> */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
