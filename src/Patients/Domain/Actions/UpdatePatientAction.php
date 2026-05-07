<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\Actions;

use Lightit\Patients\Domain\DataTransferObjects\UpdatePatientDto;
use Lightit\Patients\Domain\Models\Patient;

class UpdatePatientAction
{
    public function execute(Patient $patient, UpdatePatientDto $dto): Patient
    {
        $patient->name = $dto->name ?? $patient->name;
        $patient->email = $dto->email ?? $patient->email;

        $patient->saveOrFail();

        return $patient;
    }
}
