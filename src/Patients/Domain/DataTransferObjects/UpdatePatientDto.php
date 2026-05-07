<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\DataTransferObjects;

readonly class UpdatePatientDto
{
    public function __construct(
        public string|null $name = null,
        public string|null $email = null,
    ) {
    }
}
