<?php

declare(strict_types=1);

namespace Lightit\Authentication\App\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Patients\Domain\Models\Patient;

class MeController
{
    public function __invoke(): JsonResponse
    {
        /** @var Patient $patient */
        $patient = Auth::guard('api')->user();

        return PatientResource::make($patient)->response();
    }
}
