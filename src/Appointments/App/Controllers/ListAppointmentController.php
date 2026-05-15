<?php

declare(strict_types=1);

namespace Lightit\Appointments\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Appointments\App\Resources\AppointmentResource;
use Lightit\Appointments\Domain\Actions\ListAppointmentAction;

#[Group('Appointments')]
final readonly class ListAppointmentController
{
    #[Endpoint(
        operationId: 'listMyAppointments',
        title: 'List my appointments',
        description: 'Retrieves a paginated list of appointments belonging to the authenticated patient.'
    )]
    public function __invoke(ListAppointmentAction $action): JsonResponse
    {
        return AppointmentResource::collection($action->execute())
            ->response();
    }
}
