<?php

declare(strict_types=1);

namespace Lightit\Doctors\Domain\Actions;

use Lightit\Doctors\Domain\DataTransferObjects\StoreDoctorDto;
use Lightit\Doctors\Domain\Models\Doctor;

class StoreDoctorAction
{
    public function execute(StoreDoctorDto $dto): Doctor
    {
        $doctor = new Doctor();

        $doctor->name = $dto->name;

        $doctor->saveOrFail();

        return $doctor;
    }
}
