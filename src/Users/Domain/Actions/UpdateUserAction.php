<?php

declare(strict_types=1);

namespace Lightit\Users\Domain\Actions;

use Lightit\Users\Domain\DataTransferObjects\UpdateUserDto;
use Lightit\Users\Domain\Models\User;

class UpdateUserAction
{
    public function execute(User $user, UpdateUserDto $userDto): User
    {
        $user->name = $userDto->name ?? $user->name;
        $user->email = $userDto->emailAddress ?? $user->email;
        $user->password = $userDto->password ?? $user->password;

        $user->saveOrFail();

        return $user;
    }
}
