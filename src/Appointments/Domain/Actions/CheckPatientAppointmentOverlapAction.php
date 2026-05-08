<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\Actions;

class CheckPatientAppointmentOverlapAction extends CheckAppointmentOverlapAction
{
    protected function column(): string
    {
        return 'patient_id';
    }
}
