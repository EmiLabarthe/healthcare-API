<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Lightit\Clinics\App\Controllers\AssignDoctorToClinicController;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

describe('clinics', function (): void {
    /** @see AssignDoctorToClinicController */
    it('assigns a doctor to a clinic', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();

        postJson(url("/api/clinics/{$clinic->id}/doctors/{$doctor->id}"))
            ->assertNoContent();

        assertDatabaseHas('clinic_doctor', [
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
        ]);
    });

    it('returns 404 when the clinic does not exist', function (): void {
        $doctor = DoctorFactory::new()->createOne();

        postJson(url("/api/clinics/99999/doctors/{$doctor->id}"))
            ->assertNotFound();
    });

    it('returns 404 when the doctor does not exist', function (): void {
        $clinic = ClinicFactory::new()->createOne();

        postJson(url("/api/clinics/{$clinic->id}/doctors/99999"))
            ->assertNotFound();
    });

    it('creates a duplicate pivot row when assigning the same doctor twice', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();

        postJson(url("/api/clinics/{$clinic->id}/doctors/{$doctor->id}"))->assertNoContent();
        postJson(url("/api/clinics/{$clinic->id}/doctors/{$doctor->id}"))->assertNoContent();

        expect($clinic->doctors()->count())->toBe(2);
    });
});
