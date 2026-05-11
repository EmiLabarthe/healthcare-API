<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Lightit\Clinics\Domain\Models\Clinic;

#[Group('Clinics')]
final readonly class DeleteClinicController
{
    #[Endpoint(
        operationId: 'deleteClinic',
        title: 'Delete a clinic',
        description: 'Deletes a clinic by its ID.'
    )]
    public function __invoke(Clinic $clinic): Response
    {
        $clinic->delete();

        return response()->noContent();
    }
}
