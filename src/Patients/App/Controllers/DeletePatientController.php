<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Controllers;

use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Lightit\Patients\Domain\Models\Patient;

#[Group('Patients')]
final readonly class DeletePatientController
{
    #[Endpoint(
        operationId: 'deletePatient',
        title: 'Delete a patient',
        description: 'Deletes a patient by its ID.'
    )]
    public function __invoke(Patient $patient): Response
    {
        $patient->delete();

        return response()->noContent();
    }
}
