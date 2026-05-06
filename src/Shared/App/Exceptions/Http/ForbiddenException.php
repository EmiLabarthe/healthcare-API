<?php

declare(strict_types=1);

namespace Lightit\Shared\App\Exceptions\Http;

class ForbiddenException extends HttpException
{
    /**
     * An HTTP status code.
     */
    #[\Override]
    protected int $status = 403;

    /**
     * An error code.
     */
    #[\Override]
    protected string $errorCode = 'forbidden';
}
