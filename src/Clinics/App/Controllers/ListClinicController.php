<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Clinics\App\Resources\ClinicResource;
use Lightit\Clinics\Domain\Actions\ListClinicAction;

#[Group('Clinics')]
final readonly class ListClinicController
{
    #[Endpoint(
        operationId: 'listClinics',
        title: 'List clinics',
        description: 'Retrieves a paginated list of clinics.'
    )]
    public function __invoke(ListClinicAction $action): JsonResponse
    {
        return ClinicResource::collection($action->execute())
            ->response();
    }
}
