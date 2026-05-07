<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\DataTransferObjects;

readonly class UpdatePatientDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
    ) {
    }
}
