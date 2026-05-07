<?php

declare(strict_types=1);

namespace Lightit\Doctors\Domain\DataTransferObjects;

readonly class UpdateDoctorDto
{
    public function __construct(
        public string|null $name = null,
    ) {
    }
}
