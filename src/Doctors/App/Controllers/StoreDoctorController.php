<?php

declare(strict_types=1);

namespace Lightit\Doctors\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Doctors\App\Requests\StoreDoctorRequest;
use Lightit\Doctors\App\Resources\DoctorResource;
use Lightit\Doctors\Domain\Actions\StoreDoctorAction;

#[Group('Doctors')]
final readonly class StoreDoctorController
{
    #[Endpoint(
        operationId: 'storeDoctor',
        title: 'Create a doctor',
        description: 'Creates a new doctor.'
    )]
    public function __invoke(StoreDoctorRequest $request, StoreDoctorAction $action): JsonResponse
    {
        $doctor = $action->execute($request->toDto());

        return DoctorResource::make($doctor)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }
}
