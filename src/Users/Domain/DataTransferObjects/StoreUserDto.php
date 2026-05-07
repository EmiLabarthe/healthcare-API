<?php

declare(strict_types=1);

namespace Lightit\Users\Domain\DataTransferObjects;

use SensitiveParameter;

readonly class StoreUserDto
{
    public function __construct(
        public string $name,
        public string $emailAddress,
        #[SensitiveParameter]
        public string $password,
    ) {
    }
}
