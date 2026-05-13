<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Lightit\Clinics\App\Controllers\DeleteClinicController;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

describe('clinics', function (): void {
    /** @see DeleteClinicController */
    it('deletes an existing clinic', function (): void {
        $clinic = ClinicFactory::new()->createOne();

        deleteJson(url("/api/clinics/{$clinic->id}"))->assertNoContent();

        assertDatabaseMissing('clinics', ['id' => $clinic->id]);
    });

    it('returns 404 when deleting a missing clinic', function (): void {
        deleteJson(url('/api/clinics/99999'))->assertNotFound();
    });

    it('cascades pivot rows when deleting a clinic with assigned doctors', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic->doctors()->attach($doctor->id);

        assertDatabaseHas('clinic_doctor', [
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
        ]);

        deleteJson(url("/api/clinics/{$clinic->id}"))->assertNoContent();

        assertDatabaseMissing('clinic_doctor', ['clinic_id' => $clinic->id]);
        assertDatabaseHas('doctors', ['id' => $doctor->id]);
    });
});
