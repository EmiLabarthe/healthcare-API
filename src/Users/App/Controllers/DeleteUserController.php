<?php

declare(strict_types=1);

namespace Lightit\Users\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Lightit\Users\Domain\Models\User;

#[Group('Users')]
final readonly class DeleteUserController
{
    #[Endpoint(
        operationId: 'deleteUser',
        title: 'Delete a user',
        description: 'Deletes a user by its ID.'
    )]
    public function __invoke(User $user): Response
    {
        $user->delete();

        return response()->noContent();
    }
}
