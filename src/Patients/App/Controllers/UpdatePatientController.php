<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Patients\App\Requests\UpdatePatientRequest;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Patients\Domain\Actions\UpdatePatientAction;
use Lightit\Patients\Domain\Models\Patient;

#[Group('Patients')]
final readonly class UpdatePatientController
{
    #[Endpoint(
        operationId: 'updatePatient',
        title: 'Update a patient',
        description: 'Updates an existing patient.'
    )]
    public function __invoke(Patient $patient, UpdatePatientRequest $request, UpdatePatientAction $action): JsonResponse
    {
        $patient = $action->execute($patient, $request->toDto());

        return PatientResource::make($patient)
            ->response();
    }
}
