<?php

declare(strict_types=1);

namespace Lightit\Doctors\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Doctors\App\Resources\DoctorResource;
use Lightit\Doctors\Domain\Actions\ListDoctorAction;

#[Group('Doctors')]
final readonly class ListDoctorController
{
    #[Endpoint(
        operationId: 'listDoctors',
        title: 'List doctors',
        description: 'Retrieves a paginated list of doctors.'
    )]
    public function __invoke(ListDoctorAction $action): JsonResponse
    {
        return DoctorResource::collection($action->execute())
            ->response();
    }
}
