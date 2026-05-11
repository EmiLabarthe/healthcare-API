<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Patients\Domain\Models\Patient;

#[Group('Patients')]
final readonly class GetPatientController
{
    #[Endpoint(
        operationId: 'getPatient',
        title: 'Get a patient',
        description: 'Retrieves a patient by its ID.'
    )]
    public function __invoke(Patient $patient): JsonResponse
    {
        return PatientResource::make($patient)
            ->response();
    }
}
