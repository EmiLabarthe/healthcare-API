<?php

declare(strict_types=1);

namespace Tests\Feature\Appointments;

use Database\Factories\AppointmentFactory;
use Database\Factories\PatientFactory;
use Illuminate\Http\Request;
use Lightit\Appointments\App\Controllers\GetAppointmentController;
use Lightit\Appointments\App\Resources\AppointmentResource;
use Tests\TestCase;

use function Pest\Laravel\getJson;

describe('appointments', function (): void {
    /** @see GetAppointmentController */
    it('retrieves an appointment owned by the authenticated patient', function (): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url("/api/appointments/{$appointment->id}"))
            ->assertOk()
            ->assertJsonPath('data', AppointmentResource::make($appointment)->toArray(new Request()));
    });

    it('returns 404 when retrieving an appointment owned by another patient', function (): void {
        $owner = PatientFactory::new()->createOne();
        $intruder = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $owner->id]);

        /** @var TestCase $this */
        $this->actingAs($intruder, 'api');

        getJson(url("/api/appointments/{$appointment->id}"))
            ->assertNotFound();
    });

    it('returns 404 when the appointment does not exist', function (): void {
        $patient = PatientFactory::new()->createOne();
        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url('/api/appointments/99999'))
            ->assertNotFound();
    });

    it('returns 401 when unauthenticated', function (): void {
        $appointment = AppointmentFactory::new()->createOne();

        getJson(url("/api/appointments/{$appointment->id}"))
            ->assertUnauthorized();
    });
});
