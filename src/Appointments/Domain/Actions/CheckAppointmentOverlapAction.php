<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\Actions;

use Carbon\CarbonImmutable;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Lightit\Doctors\Domain\Models\Doctor;
use Lightit\Patients\Domain\Models\Patient;

class CheckAppointmentOverlapAction
{
    /**
     * @param  class-string<Patient|Doctor>  $model
     */
    public function execute(
        string $model,
        mixed $key,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        ?int $excludeAppointmentId = null,
    ): bool {
        $parent = new $model;
        $parent->{$parent->getKeyName()} = $key;

        return $parent->appointments()
            ->whereNot('status', AppointmentStatus::Cancelled)
            ->when(
                $excludeAppointmentId,
                fn ($query, int $excludeId) => $query->whereKeyNot($excludeId),
            )
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }
}
