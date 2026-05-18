<?php

declare(strict_types=1);

namespace Tests\Feature\Appointments;

use Database\Factories\AppointmentFactory;
use Database\Factories\PatientFactory;
use Lightit\Appointments\App\Controllers\CancelAppointmentController;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Lightit\Appointments\Domain\Models\Appointment;
use Tests\TestCase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

describe('appointments', function (): void {
    /** @see CancelAppointmentController */
    it('cancels an owned scheduled appointment', function (): void {
        /** @var TestCase $this */
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'status'     => AppointmentStatus::Scheduled,
        ]);

        $this->actingAs($patient, 'api');

        postJson(url("/api/appointments/{$appointment->id}/cancel"))->assertNoContent();

        assertDatabaseHas(Appointment::class, [
            'id'     => $appointment->id,
            'status' => AppointmentStatus::Cancelled->value,
        ]);
    });

    it('is idempotent: cancelling an already-cancelled appointment leaves it cancelled', function (): void {
        /** @var TestCase $this */
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->cancelled()->createOne(['patient_id' => $patient->id]);

        $this->actingAs($patient, 'api');

        postJson(url("/api/appointments/{$appointment->id}/cancel"))->assertNoContent();

        assertDatabaseHas(Appointment::class, [
            'id'     => $appointment->id,
            'status' => AppointmentStatus::Cancelled->value,
        ]);
    });

    it('returns 404 when cancelling an appointment owned by another patient', function (): void {
        /** @var TestCase $this */
        $owner = PatientFactory::new()->createOne();
        $intruder = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne([
            'patient_id' => $owner->id,
            'status'     => AppointmentStatus::Scheduled,
        ]);

        $this->actingAs($intruder, 'api');

        postJson(url("/api/appointments/{$appointment->id}/cancel"))->assertNotFound();

        assertDatabaseHas(Appointment::class, [
            'id'     => $appointment->id,
            'status' => AppointmentStatus::Scheduled->value,
        ]);
    });

    it('returns 404 when cancelling a missing appointment', function (): void {
        /** @var TestCase $this */
        $patient = PatientFactory::new()->createOne();
        $this->actingAs($patient, 'api');

        postJson(url('/api/appointments/99999/cancel'))->assertNotFound();
    });

    it('returns 401 when unauthenticated', function (): void {
        $appointment = AppointmentFactory::new()->createOne();

        postJson(url("/api/appointments/{$appointment->id}/cancel"))->assertUnauthorized();
    });
});
