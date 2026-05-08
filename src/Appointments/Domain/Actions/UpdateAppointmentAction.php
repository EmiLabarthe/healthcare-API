<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\Actions;

use Lightit\Appointments\Domain\DataTransferObjects\UpdateAppointmentDto;
use Lightit\Appointments\Domain\Models\Appointment;

class UpdateAppointmentAction
{
    public function execute(Appointment $appointment, UpdateAppointmentDto $dto): Appointment
    {
        $appointment->doctor_id = $dto->doctorId ?? $appointment->doctor_id;
        $appointment->starts_at = $dto->startsAt ?? $appointment->starts_at;
        $appointment->ends_at = $dto->endsAt ?? $appointment->ends_at;

        $appointment->saveOrFail();

        return $appointment;
    }
}
