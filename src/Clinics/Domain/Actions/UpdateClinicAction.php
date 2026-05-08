<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\Actions;

use Lightit\Clinics\Domain\DataTransferObjects\UpdateClinicDto;
use Lightit\Clinics\Domain\Models\Clinic;

class UpdateClinicAction
{
    public function execute(Clinic $clinic, UpdateClinicDto $dto): Clinic
    {
        $clinic->name = $dto->name ?? $clinic->name;
        $clinic->address = $dto->address ?? $clinic->address;

        if ($clinic->isDirty()) {
            $clinic->saveOrFail();
        }

        return $clinic;
    }
}
