<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\Actions;

use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Lightit\Appointments\Domain\Models\Appointment;

class CancelAppointmentAction
{
    public function execute(Appointment $appointment): void
    {
        $appointment->status = AppointmentStatus::Cancelled;
        $appointment->saveOrFail();
    }
}
