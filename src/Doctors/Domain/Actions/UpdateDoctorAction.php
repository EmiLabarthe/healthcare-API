<?php

declare(strict_types=1);

namespace Lightit\Doctors\Domain\Actions;

use Lightit\Doctors\Domain\DataTransferObjects\UpdateDoctorDto;
use Lightit\Doctors\Domain\Models\Doctor;

class UpdateDoctorAction
{
    public function execute(Doctor $doctor, UpdateDoctorDto $dto): Doctor
    {
        $doctor->name = $dto->name ?? $doctor->name;

        $doctor->saveOrFail();

        return $doctor;
    }
}
