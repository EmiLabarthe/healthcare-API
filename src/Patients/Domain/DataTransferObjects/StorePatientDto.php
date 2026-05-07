<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\DataTransferObjects;

readonly class StorePatientDto
{
    public function __construct(
        public string $name,
        public string $email,
    ) {
    }
}
