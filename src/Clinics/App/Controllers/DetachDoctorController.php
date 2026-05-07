<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Lightit\Clinics\Domain\Actions\DetachDoctorAction;
use Lightit\Clinics\Domain\Models\Clinic;
use Lightit\Doctors\Domain\Models\Doctor;

#[Group('Clinics')]
final readonly class DetachDoctorController
{
    #[Endpoint(
        operationId: 'detachDoctor',
        title: 'Detach a doctor from a clinic',
        description: 'Removes the association between a doctor and a clinic.'
    )]
    public function __invoke(Clinic $clinic, Doctor $doctor, DetachDoctorAction $action): Response
    {
        $action->execute($clinic, $doctor);

        return response()->noContent();
    }
}
