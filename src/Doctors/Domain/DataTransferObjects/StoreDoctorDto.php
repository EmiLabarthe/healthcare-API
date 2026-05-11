<?php

declare(strict_types=1);

namespace Lightit\Doctors\Domain\DataTransferObjects;

readonly class StoreDoctorDto
{
    public function __construct(
        public string $name,
    ) {
    }
}
