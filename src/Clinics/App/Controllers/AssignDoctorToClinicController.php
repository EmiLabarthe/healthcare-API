<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Lightit\Clinics\Domain\Actions\AssignDoctorToClinicAction;
use Lightit\Clinics\Domain\Models\Clinic;
use Lightit\Doctors\Domain\Models\Doctor;

#[Group('Clinics')]
final readonly class AssignDoctorToClinicController
{
    #[Endpoint(
        operationId: 'assignDoctorToClinic',
        title: 'Assign a doctor to a clinic',
        description: 'Associates an existing doctor with a clinic.'
    )]
    public function __invoke(Clinic $clinic, Doctor $doctor, AssignDoctorToClinicAction $action): Response
    {
        $action->execute($clinic, $doctor);

        return response()->noContent();
    }
}
