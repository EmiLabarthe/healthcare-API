<?php

declare(strict_types=1);

namespace Lightit\Users\Domain\DataTransferObjects;

use SensitiveParameter;

readonly class UpdateUserDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $emailAddress = null,
        #[SensitiveParameter]
        public ?string $password = null,
    ) {
    }
}
