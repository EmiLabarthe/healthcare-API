<?php

declare(strict_types=1);

namespace Tests\Feature\Appointments;

use Carbon\CarbonImmutable;
use Database\Factories\AppointmentFactory;
use Database\Factories\DoctorFactory;
use Database\Factories\PatientFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Appointments\App\Controllers\UpdateAppointmentController;
use Lightit\Appointments\App\Resources\AppointmentResource;
use Tests\TestCase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

dataset('update-appointment-validation', [
    'doctor_id not integer' => ['doctor_id', 'abc'],
    'starts_at not a date'  => ['starts_at', 'not-a-date'],
    'ends_at not a date'    => ['ends_at', 'not-a-date'],
]);

describe('appointments', function (): void {
    /** @see UpdateAppointmentController */
    it('updates all editable fields of an owned appointment', function (): void {
        $patient = PatientFactory::new()->createOne();
        $oldDoctor = DoctorFactory::new()->createOne();
        $newDoctor = DoctorFactory::new()->createOne();

        $appointment = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'doctor_id'  => $oldDoctor->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        $response = patchJson(url("/api/appointments/{$appointment->id}"), [
            'doctor_id' => $newDoctor->id,
            'starts_at' => CarbonImmutable::parse('+3 days 14:00:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+3 days 15:00:00')->toIso8601String(),
        ]);

        $appointment->refresh();

        /** @var array<string, mixed> $resourceData */
        $resourceData = json_decode(
            (string) json_encode(AppointmentResource::make($appointment)->resolve()),
            true,
        );

        $response->assertOk()->assertJson(
            fn (AssertableJson $json): AssertableJson => $json->has(
                'data',
                fn (AssertableJson $json): AssertableJson => $json->whereAll($resourceData)
            )
        );

        assertDatabaseHas('appointments', [
            'id'        => $appointment->id,
            'doctor_id' => $newDoctor->id,
        ]);
    });

    it('updates only the provided fields', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();

        $appointment = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        $newStartsAt = CarbonImmutable::parse('+4 days 09:00:00');
        $newEndsAt = CarbonImmutable::parse('+4 days 10:00:00');

        patchJson(url("/api/appointments/{$appointment->id}"), [
            'starts_at' => $newStartsAt->toIso8601String(),
            'ends_at'   => $newEndsAt->toIso8601String(),
        ])->assertOk();

        $appointment->refresh();

        expect($appointment->doctor_id)->toBe($doctor->id)
            ->and($appointment->starts_at->equalTo($newStartsAt))->toBeTrue()
            ->and($appointment->ends_at->equalTo($newEndsAt))->toBeTrue();
    });

    it('accepts an empty payload as a no-op', function (): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        patchJson(url("/api/appointments/{$appointment->id}"), [])->assertOk();
    });

    it('returns 404 when updating an appointment owned by another patient', function (): void {
        $owner = PatientFactory::new()->createOne();
        $intruder = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $owner->id]);

        /** @var TestCase $this */
        $this->actingAs($intruder, 'api');

        patchJson(url("/api/appointments/{$appointment->id}"), [])->assertNotFound();
    });

    it('returns 404 when updating a missing appointment', function (): void {
        $patient = PatientFactory::new()->createOne();
        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        patchJson(url('/api/appointments/99999'), [])->assertNotFound();
    });

    it('returns 401 when unauthenticated', function (): void {
        $appointment = AppointmentFactory::new()->createOne();

        patchJson(url("/api/appointments/{$appointment->id}"), [])->assertUnauthorized();
    });

    it('rejects invalid field values', function (string $field, string $value): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        patchJson(url("/api/appointments/{$appointment->id}"), [$field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'error.fields');
    })->with('update-appointment-validation');

    it('rejects a non-existent doctor_id', function (): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        patchJson(url("/api/appointments/{$appointment->id}"), ['doctor_id' => 99999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['doctor_id'], 'error.fields');
    });

    it('rejects a starts_at that is in the past', function (): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        patchJson(url("/api/appointments/{$appointment->id}"), [
            'starts_at' => CarbonImmutable::parse('-1 day')->toIso8601String(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['starts_at'], 'error.fields');
    });

    it('rejects an ends_at that is not strictly after starts_at', function (): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        $startsAt = CarbonImmutable::parse('+2 days 10:00:00')->toIso8601String();

        patchJson(url("/api/appointments/{$appointment->id}"), [
            'starts_at' => $startsAt,
            'ends_at'   => $startsAt,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ends_at'], 'error.fields');
    });

    it('rejects when the new window overlaps another scheduled appointment for the same doctor', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();

        AppointmentFactory::new()->createOne([
            'doctor_id'  => $doctor->id,
            'patient_id' => PatientFactory::new()->createOne()->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:00:00'),
        ]);

        $appointment = AppointmentFactory::new()->createOne([
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 14:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 15:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        patchJson(url("/api/appointments/{$appointment->id}"), [
            'starts_at' => CarbonImmutable::parse('+2 days 10:30:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:30:00')->toIso8601String(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['starts_at'], 'error.fields');
    });

    it('allows an update that overlaps the appointment being edited (self-exclusion)', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();

        $appointment = AppointmentFactory::new()->createOne([
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        patchJson(url("/api/appointments/{$appointment->id}"), [
            'starts_at' => CarbonImmutable::parse('+2 days 10:30:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:30:00')->toIso8601String(),
        ])->assertOk();
    });

    it('uses the existing appointment values when fields are omitted from payload', function (): void {
        $patient = PatientFactory::new()->createOne();
        $oldDoctor = DoctorFactory::new()->createOne();
        $newDoctor = DoctorFactory::new()->createOne();

        AppointmentFactory::new()->createOne([
            'doctor_id'  => $newDoctor->id,
            'patient_id' => PatientFactory::new()->createOne()->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:30:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:30:00'),
        ]);

        $appointment = AppointmentFactory::new()->createOne([
            'doctor_id'  => $oldDoctor->id,
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        patchJson(url("/api/appointments/{$appointment->id}"), [
            'doctor_id' => $newDoctor->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['starts_at'], 'error.fields');
    });
});
