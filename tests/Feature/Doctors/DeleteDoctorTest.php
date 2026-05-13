<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Lightit\Doctors\App\Controllers\DeleteDoctorController;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

describe('doctors', function (): void {
    /** @see DeleteDoctorController */
    it('deletes an existing doctor', function (): void {
        $doctor = DoctorFactory::new()->createOne();

        deleteJson(url("/api/doctors/{$doctor->id}"))->assertNoContent();

        assertDatabaseMissing('doctors', ['id' => $doctor->id]);
    });

    it('returns 404 when deleting a missing doctor', function (): void {
        deleteJson(url('/api/doctors/99999'))->assertNotFound();
    });

    it('cascades pivot rows when deleting a doctor with assigned clinics', function (): void {
        $doctor = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctor->clinics()->attach($clinic->id);

        assertDatabaseHas('clinic_doctor', [
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
        ]);

        deleteJson(url("/api/doctors/{$doctor->id}"))->assertNoContent();

        assertDatabaseMissing('clinic_doctor', ['doctor_id' => $doctor->id]);
        assertDatabaseHas('clinics', ['id' => $clinic->id]);
    });
});
