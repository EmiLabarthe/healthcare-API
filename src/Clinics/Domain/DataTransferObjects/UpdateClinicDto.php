<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\DataTransferObjects;

readonly class UpdateClinicDto
{
    public function __construct(
        public string|null $name = null,
        public string|null $address = null,
    ) {
    }
}
