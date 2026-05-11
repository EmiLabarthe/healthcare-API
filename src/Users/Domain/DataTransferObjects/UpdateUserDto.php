<?php

declare(strict_types=1);

namespace Lightit\Users\Domain\DataTransferObjects;

use SensitiveParameter;

readonly class UpdateUserDto
{
    public function __construct(
        public string|null $name = null,
        public string|null $emailAddress = null,
        #[SensitiveParameter]
        public string|null $password = null,
    ) {
    }
}
