<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\AppointmentFactory;
use Database\Factories\PatientFactory;
use Lightit\Patients\App\Controllers\DeletePatientController;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

describe('patients', function (): void {
    /** @see DeletePatientController */
    it('deletes an existing patient', function (): void {
        $patient = PatientFactory::new()->createOne();

        deleteJson(url("/api/patients/{$patient->id}"))->assertNoContent();

        assertDatabaseMissing('patients', ['id' => $patient->id]);
    });

    it('returns 404 when deleting a missing patient', function (): void {
        deleteJson(url('/api/patients/99999'))->assertNotFound();
    });

    it('returns 404 for a non-numeric patient id', function (): void {
        deleteJson(url('/api/patients/not-a-number'))->assertNotFound();
    });

    it('cascades to the patient appointments via the database FK', function (): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        assertDatabaseHas('appointments', ['id' => $appointment->id]);

        deleteJson(url("/api/patients/{$patient->id}"))->assertNoContent();

        assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    });

    it('does not delete unrelated patients', function (): void {
        $target = PatientFactory::new()->createOne();
        $bystander = PatientFactory::new()->createOne();

        deleteJson(url("/api/patients/{$target->id}"))->assertNoContent();

        assertDatabaseMissing('patients', ['id' => $target->id]);
        assertDatabaseHas('patients', ['id' => $bystander->id]);
    });
});
