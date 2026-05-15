<?php

declare(strict_types=1);

namespace Tests\Feature\Appointments;

use Database\Factories\AppointmentFactory;
use Database\Factories\PatientFactory;
use Lightit\Appointments\App\Controllers\DeleteAppointmentController;
use Lightit\Appointments\Domain\Models\Appointment;
use Tests\TestCase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\deleteJson;

describe('appointments', function (): void {
    /** @see DeleteAppointmentController */
    it('soft-deletes an owned appointment', function (): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        deleteJson(url("/api/appointments/{$appointment->id}"))->assertNoContent();

        assertSoftDeleted(Appointment::class, ['id' => $appointment->id]);
        assertDatabaseHas(Appointment::class, ['id' => $appointment->id]);
        expect(Appointment::withTrashed()->find($appointment->id))->not->toBeNull();
    });

    it('returns 404 when deleting an appointment owned by another patient', function (): void {
        $owner = PatientFactory::new()->createOne();
        $intruder = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $owner->id]);

        /** @var TestCase $this */
        $this->actingAs($intruder, 'api');

        deleteJson(url("/api/appointments/{$appointment->id}"))->assertNotFound();

        assertDatabaseHas(Appointment::class, ['id' => $appointment->id, 'deleted_at' => null]);
    });

    it('returns 404 when deleting a missing appointment', function (): void {
        $patient = PatientFactory::new()->createOne();
        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        deleteJson(url('/api/appointments/99999'))->assertNotFound();
    });

    it('returns 401 when unauthenticated', function (): void {
        $appointment = AppointmentFactory::new()->createOne();

        deleteJson(url("/api/appointments/{$appointment->id}"))->assertUnauthorized();
    });
});
