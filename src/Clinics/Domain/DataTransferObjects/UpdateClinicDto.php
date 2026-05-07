<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\DataTransferObjects;

readonly class UpdateClinicDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $address = null,
    ) {
    }
}
