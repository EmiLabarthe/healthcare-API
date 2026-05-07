<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\Actions;

use Lightit\Clinics\Domain\Models\Clinic;
use Lightit\Doctors\Domain\Models\Doctor;

class AttachDoctorAction
{
    public function execute(Clinic $clinic, Doctor $doctor): void
    {
        $clinic->doctors()->attach($doctor->id);
    }
}
