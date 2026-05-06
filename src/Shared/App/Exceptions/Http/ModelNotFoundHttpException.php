<?php

declare(strict_types=1);

namespace Lightit\Shared\App\Exceptions\Http;

class ModelNotFoundHttpException extends HttpException
{
    /**
     * An HTTP status code.
     */
    #[\Override]
    protected int $status = 404;

    /**
     * An error code.
     */
    #[\Override]
    protected string $errorCode = 'entity_not_found';
}
