<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Clinics\App\Resources\ClinicResource;
use Lightit\Clinics\Domain\Models\Clinic;

#[Group('Clinics')]
final readonly class GetClinicController
{
    #[Endpoint(
        operationId: 'getClinic',
        title: 'Get a clinic',
        description: 'Retrieves a clinic by its ID.'
    )]
    public function __invoke(Clinic $clinic): JsonResponse
    {
        $clinic->loadCount('doctors');

        return ClinicResource::make($clinic)
            ->response();
    }
}
