<?php

declare(strict_types=1);

namespace Lightit\Shared\App\Exceptions\Http;

class InvalidActionException extends HttpException
{
    /**
     * The HTTP status code.
     */
    #[\Override]
    protected int $status = 422;

    /**
     * The error code.
     */
    #[\Override]
    protected string $errorCode = 'invalid_action';

    /**
     * The error message.
     *
     * @var string
     */
    #[\Override]
    protected $message = 'This is an invalid action';
}
