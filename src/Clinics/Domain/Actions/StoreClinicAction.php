<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\Actions;

use Lightit\Clinics\Domain\DataTransferObjects\StoreClinicDto;
use Lightit\Clinics\Domain\Models\Clinic;

class StoreClinicAction
{
    public function execute(StoreClinicDto $dto): Clinic
    {
        $clinic = new Clinic();

        $clinic->name = $dto->name;
        $clinic->address = $dto->address;

        $clinic->saveOrFail();

        return $clinic;
    }
}
