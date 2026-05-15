<?php

declare(strict_types=1);

namespace Lightit\Authentication\App\Controllers;

use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Patients\Domain\Models\Patient;

class MeController
{
    public function __invoke(#[CurrentUser] Patient $patient): JsonResponse
    {
        return PatientResource::make($patient)->response();
    }
}
