<?php

declare(strict_types=1);

namespace Lightit\Appointments\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Appointments\App\Requests\UpdateAppointmentRequest;
use Lightit\Appointments\App\Resources\AppointmentResource;
use Lightit\Appointments\Domain\Actions\UpdateAppointmentAction;
use Lightit\Appointments\Domain\Models\Appointment;

#[Group('Appointments')]
final readonly class UpdateAppointmentController
{
    #[Endpoint(
        operationId: 'updateAppointment',
        title: 'Update an appointment',
        description: 'Updates an existing appointment.'
    )]
    public function __invoke(
        Appointment $appointment,
        UpdateAppointmentRequest $request,
        UpdateAppointmentAction $action,
    ): JsonResponse {
        $appointment = $action->execute($appointment, $request->toDto());

        return AppointmentResource::make($appointment)
            ->response();
    }
}
