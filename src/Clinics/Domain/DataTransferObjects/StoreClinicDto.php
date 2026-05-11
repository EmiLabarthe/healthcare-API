<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\DataTransferObjects;

readonly class StoreClinicDto
{
    public function __construct(
        public string $name,
        public string $address,
    ) {
    }
}
