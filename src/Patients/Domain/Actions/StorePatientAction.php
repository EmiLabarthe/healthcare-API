<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\Actions;

use Lightit\Patients\Domain\DataTransferObjects\StorePatientDto;
use Lightit\Patients\Domain\Models\Patient;

class StorePatientAction
{
    public function execute(StorePatientDto $dto): Patient
    {
        $patient = new Patient();

        $patient->name = $dto->name;
        $patient->email = $dto->email;

        $patient->saveOrFail();

        return $patient;
    }
}
