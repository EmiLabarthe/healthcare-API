<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Lightit\Clinics\App\Controllers\RemoveDoctorFromClinicController;
use Lightit\Clinics\Domain\Models\ClinicDoctor;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

describe('clinics', function (): void {
    /** @see RemoveDoctorFromClinicController */
    it('removes a doctor from a clinic', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic->doctors()->attach($doctor->id);

        deleteJson(url("/api/clinics/{$clinic->id}/doctors/{$doctor->id}"))
            ->assertNoContent();

        assertDatabaseMissing(ClinicDoctor::class, [
            'clinic_id' => $clinic->id,
            'doctor_id' => $doctor->id,
        ]);
    });

    it('returns no content when the doctor was not assigned', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();

        deleteJson(url("/api/clinics/{$clinic->id}/doctors/{$doctor->id}"))
            ->assertNoContent();

        expect($clinic->doctors()->count())->toBe(0);
    });

    it('returns 404 when the clinic does not exist', function (): void {
        $doctor = DoctorFactory::new()->createOne();

        deleteJson(url("/api/clinics/99999/doctors/{$doctor->id}"))
            ->assertNotFound();
    });

    it('returns 404 when the doctor does not exist', function (): void {
        $clinic = ClinicFactory::new()->createOne();

        deleteJson(url("/api/clinics/{$clinic->id}/doctors/99999"))
            ->assertNotFound();
    });

    it('only removes the targeted pairing', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $doctorToKeep = DoctorFactory::new()->createOne();
        $doctorToDrop = DoctorFactory::new()->createOne();

        $clinic->doctors()->attach([$doctorToKeep->id, $doctorToDrop->id]);

        deleteJson(url("/api/clinics/{$clinic->id}/doctors/{$doctorToDrop->id}"))
            ->assertNoContent();

        expect($clinic->doctors()->pluck('doctors.id')->all())
            ->toBe([$doctorToKeep->id]);
    });
});
