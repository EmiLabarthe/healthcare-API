<?php

declare(strict_types=1);

namespace Lightit\Appointments\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Lightit\Appointments\App\Requests\StoreAppointmentRequest;
use Lightit\Appointments\App\Resources\AppointmentResource;
use Lightit\Appointments\Domain\Actions\StoreAppointmentAction;
use Lightit\Patients\Domain\Models\Patient;

#[Group('Appointments')]
final readonly class StoreAppointmentController
{
    #[Endpoint(
        operationId: 'storeAppointment',
        title: 'Create an appointment',
        description: 'Schedules a new appointment for a doctor and patient at a clinic.'
    )]
    public function __invoke(
        StoreAppointmentRequest $request,
        StoreAppointmentAction $action,
        #[CurrentUser]
        Patient $patient,
    ): JsonResponse {
        $appointment = $action->execute($request->toDto($patient));

        return AppointmentResource::make($appointment)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }
}
