<?php

declare(strict_types=1);

namespace Lightit\Users\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Users\App\Resources\UserResource;
use Lightit\Users\Domain\Models\User;

#[Group('Users')]
final readonly class GetUserController
{
    #[Endpoint(
        operationId: 'getUser',
        title: 'Get a single user',
        description: 'Retrieves a user by its ID.'
    )]
    public function __invoke(User $user): JsonResponse
    {
        return UserResource::make($user)
            ->response();
    }
}
