<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Clinics\App\Requests\StoreClinicRequest;
use Lightit\Clinics\App\Resources\ClinicResource;
use Lightit\Clinics\Domain\Actions\StoreClinicAction;

#[Group('Clinics')]
final readonly class StoreClinicController
{
    #[Endpoint(
        operationId: 'storeClinic',
        title: 'Create a clinic',
        description: 'Creates a new clinic.'
    )]
    public function __invoke(StoreClinicRequest $request, StoreClinicAction $action): JsonResponse
    {
        $clinic = $action->execute($request->toDto());
        $clinic->loadCount('doctors');

        return ClinicResource::make($clinic)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }
}
