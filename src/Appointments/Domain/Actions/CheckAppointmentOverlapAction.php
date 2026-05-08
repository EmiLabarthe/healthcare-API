<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\Actions;

use Carbon\CarbonImmutable;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Lightit\Appointments\Domain\Models\Appointment;

abstract class CheckAppointmentOverlapAction
{
    abstract protected function column(): string;

    public function execute(
        int $id,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        ?int $excludeAppointmentId = null,
    ): bool {
        return Appointment::query()
            ->where($this->column(), $id)
            ->where('status', '!=', AppointmentStatus::Cancelled)
            ->when(
                $excludeAppointmentId,
                fn ($query, int $excludeId) => $query->whereKeyNot($excludeId),
            )
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }
}
