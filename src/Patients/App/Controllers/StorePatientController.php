<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Patients\App\Requests\StorePatientRequest;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Patients\Domain\Actions\StorePatientAction;

#[Group('Patients')]
final readonly class StorePatientController
{
    #[Endpoint(
        operationId: 'storePatient',
        title: 'Create a patient',
        description: 'Creates a new patient.'
    )]
    public function __invoke(StorePatientRequest $request, StorePatientAction $action): JsonResponse
    {
        $patient = $action->execute($request->toDto());

        return PatientResource::make($patient)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }
}
