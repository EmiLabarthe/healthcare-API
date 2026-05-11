<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int                     $id
 * @property int                     $clinic_id
 * @property int                     $doctor_id
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClinicDoctor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClinicDoctor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClinicDoctor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClinicDoctor whereClinicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClinicDoctor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClinicDoctor whereDoctorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClinicDoctor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClinicDoctor whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ClinicDoctor extends Pivot
{
    #[\Override]
    public $incrementing = true;
}
