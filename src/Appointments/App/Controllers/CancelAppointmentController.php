<?php

declare(strict_types=1);

namespace Lightit\Appointments\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Lightit\Appointments\Domain\Actions\CancelAppointmentAction;
use Lightit\Appointments\Domain\Models\Appointment;

#[Group('Appointments')]
final readonly class CancelAppointmentController
{
    #[Endpoint(
        operationId: 'cancelAppointment',
        title: 'Cancel an appointment',
        description: 'Cancels an appointment by setting its status to cancelled and soft-deleting it.'
    )]
    public function __invoke(Appointment $appointment, CancelAppointmentAction $action): Response
    {
        $action->execute($appointment);

        return response()->noContent();
    }
}
