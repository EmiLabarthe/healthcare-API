<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\Actions;

class CheckDoctorAppointmentOverlapAction extends CheckAppointmentOverlapAction
{
    protected function column(): string
    {
        return 'doctor_id';
    }
}
