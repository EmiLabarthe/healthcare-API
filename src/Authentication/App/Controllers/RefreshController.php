<?php

declare(strict_types=1);

namespace Lightit\Authentication\App\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Lightit\Authentication\App\Resources\LoginResource;
use Lightit\Authentication\Domain\DataTransferObjects\LoginDto;
use PHPOpenSourceSaver\JWTAuth\Factory as JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class RefreshController
{
    public function __invoke(JWTAuth $jwtAuth): JsonResponse
    {
        /** @var JWTGuard $guard */
        $guard = Auth::guard();

        $token = $guard->refresh();

        $loginDto = new LoginDto(
            accessToken: $token,
            tokenType: 'bearer',
            expiresIn: $jwtAuth->getTTL() * 60,
        );

        return LoginResource::make($loginDto)
            ->response();
    }
}
