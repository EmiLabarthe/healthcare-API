<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Lightit\Clinics\App\Resources\ClinicResource;
use Lightit\Clinics\Domain\Actions\AttachDoctorAction;
use Lightit\Clinics\Domain\Models\Clinic;
use Lightit\Doctors\App\Resources\DoctorResource;
use Lightit\Doctors\Domain\Models\Doctor;

#[Group('Clinics')]
final readonly class AttachDoctorController
{
    #[Endpoint(
        operationId: 'attachDoctor',
        title: 'Attach a doctor to a clinic',
        description: 'Associates an existing doctor with a clinic.'
    )]
    public function __invoke(Clinic $clinic, Doctor $doctor, AttachDoctorAction $action): JsonResponse
    {
        $action->execute($clinic, $doctor);

        return response()->json([
            'data' => [
                'clinic' => ClinicResource::make($clinic),
                'doctor' => DoctorResource::make($doctor),
            ],
        ], JsonResponse::HTTP_CREATED);
    }
}
