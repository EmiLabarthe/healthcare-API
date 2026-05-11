<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Patients\Domain\Actions\ListPatientAction;

#[Group('Patients')]
final readonly class ListPatientController
{
    #[Endpoint(
        operationId: 'listPatients',
        title: 'List patients',
        description: 'Retrieves a paginated list of patients.'
    )]
    public function __invoke(ListPatientAction $action): JsonResponse
    {
        return PatientResource::collection($action->execute())
            ->response();
    }
}
