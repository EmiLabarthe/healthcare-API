<?php

declare(strict_types=1);

namespace Tests\Feature\Appointments;

use Carbon\CarbonImmutable;
use Database\Factories\AppointmentFactory;
use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Database\Factories\PatientFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Appointments\App\Controllers\StoreAppointmentController;
use Lightit\Appointments\App\Resources\AppointmentResource;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Lightit\Appointments\Domain\Models\Appointment;
use Tests\TestCase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

dataset('store-appointment-validation', [
    'doctor_id required'    => ['doctor_id', ''],
    'doctor_id not integer' => ['doctor_id', 'abc'],
    'clinic_id required'    => ['clinic_id', ''],
    'clinic_id not integer' => ['clinic_id', 'abc'],
    'starts_at required'    => ['starts_at', ''],
    'starts_at not a date'  => ['starts_at', 'not-a-date'],
    'ends_at required'      => ['ends_at', ''],
    'ends_at not a date'    => ['ends_at', 'not-a-date'],
]);

describe('appointments', function (): void {
    /** @see StoreAppointmentController */
    it('creates an appointment successfully', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctor->clinics()->attach($clinic->id);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        $payload = [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'starts_at' => CarbonImmutable::parse('+2 days 10:00:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:00:00')->toIso8601String(),
        ];

        $response = postJson(url('/api/appointments'), $payload);

        $appointment = Appointment::query()->latest('id')->firstOrFail();

        /** @var array<string, mixed> $resourceData */
        $resourceData = json_decode(
            (string) json_encode(AppointmentResource::make($appointment)->resolve()),
            true,
        );

        $response
            ->assertCreated()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll($resourceData)
                )
            );

        assertDatabaseHas(Appointment::class, [
            'id'         => $appointment->id,
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
            'clinic_id'  => $clinic->id,
            'status'     => AppointmentStatus::Scheduled->value,
        ]);
    });

    it('rejects unauthenticated requests with 401', function (): void {
        postJson(url('/api/appointments'), [])
            ->assertUnauthorized();
    });

    it('forces patient_id to the authenticated user even if one is sent in payload', function (): void {
        $authPatient = PatientFactory::new()->createOne();
        $otherPatient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctor->clinics()->attach($clinic->id);

        /** @var TestCase $this */
        $this->actingAs($authPatient, 'api');

        postJson(url('/api/appointments'), [
            'doctor_id'  => $doctor->id,
            'clinic_id'  => $clinic->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:00:00')->toIso8601String(),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:00:00')->toIso8601String(),
            'patient_id' => $otherPatient->id,
            'status'     => AppointmentStatus::Cancelled->value,
        ])->assertCreated();

        assertDatabaseHas(Appointment::class, [
            'patient_id' => $authPatient->id,
            'status'     => AppointmentStatus::Scheduled->value,
        ]);
        assertDatabaseMissing(Appointment::class, ['patient_id' => $otherPatient->id]);
    });

    it('rejects invalid field values', function (string $field, string $value): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctor->clinics()->attach($clinic->id);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        $payload = [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'starts_at' => CarbonImmutable::parse('+2 days 10:00:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:00:00')->toIso8601String(),
        ];

        postJson(url('/api/appointments'), [...$payload, $field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'error.fields');
    })->with('store-appointment-validation');

    it('rejects a non-existent doctor_id', function (): void {
        $patient = PatientFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        postJson(url('/api/appointments'), [
            'doctor_id' => 99999,
            'clinic_id' => $clinic->id,
            'starts_at' => CarbonImmutable::parse('+2 days 10:00:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:00:00')->toIso8601String(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['doctor_id'], 'error.fields');
    });

    it('rejects a clinic_id that exists but is not linked to the doctor', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $unlinkedClinic = ClinicFactory::new()->createOne();

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        postJson(url('/api/appointments'), [
            'doctor_id' => $doctor->id,
            'clinic_id' => $unlinkedClinic->id,
            'starts_at' => CarbonImmutable::parse('+2 days 10:00:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:00:00')->toIso8601String(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['clinic_id'], 'error.fields');
    });

    it('rejects a starts_at that is in the past', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctor->clinics()->attach($clinic->id);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        postJson(url('/api/appointments'), [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'starts_at' => CarbonImmutable::parse('-1 day 10:00:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('-1 day 11:00:00')->toIso8601String(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['starts_at'], 'error.fields');
    });

    it('rejects an ends_at that is not strictly after starts_at', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctor->clinics()->attach($clinic->id);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        $startsAt = CarbonImmutable::parse('+2 days 10:00:00')->toIso8601String();

        postJson(url('/api/appointments'), [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'starts_at' => $startsAt,
            'ends_at'   => $startsAt,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ends_at'], 'error.fields');
    });

    it('rejects when the doctor already has an overlapping scheduled appointment', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctor->clinics()->attach($clinic->id);

        AppointmentFactory::new()->createOne([
            'doctor_id'  => $doctor->id,
            'patient_id' => PatientFactory::new()->createOne()->id,
            'clinic_id'  => $clinic->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        postJson(url('/api/appointments'), [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'starts_at' => CarbonImmutable::parse('+2 days 10:30:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:30:00')->toIso8601String(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['starts_at'], 'error.fields');
    });

    it('rejects when the patient already has an overlapping scheduled appointment', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctorA = DoctorFactory::new()->createOne();
        $doctorB = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctorA->clinics()->attach($clinic->id);
        $doctorB->clinics()->attach($clinic->id);

        AppointmentFactory::new()->createOne([
            'doctor_id'  => $doctorA->id,
            'patient_id' => $patient->id,
            'clinic_id'  => $clinic->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        postJson(url('/api/appointments'), [
            'doctor_id' => $doctorB->id,
            'clinic_id' => $clinic->id,
            'starts_at' => CarbonImmutable::parse('+2 days 10:30:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:30:00')->toIso8601String(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['starts_at'], 'error.fields');
    });

    it('allows creation when an overlapping appointment exists but is cancelled', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctor->clinics()->attach($clinic->id);

        AppointmentFactory::new()->cancelled()->createOne([
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
            'clinic_id'  => $clinic->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 10:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 11:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        postJson(url('/api/appointments'), [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'starts_at' => CarbonImmutable::parse('+2 days 10:00:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:00:00')->toIso8601String(),
        ])->assertCreated();
    });

    it('allows booking that starts exactly when another ends (touching boundary)', function (): void {
        $patient = PatientFactory::new()->createOne();
        $doctor = DoctorFactory::new()->createOne();
        $clinic = ClinicFactory::new()->createOne();
        $doctor->clinics()->attach($clinic->id);

        AppointmentFactory::new()->createOne([
            'doctor_id'  => $doctor->id,
            'patient_id' => PatientFactory::new()->createOne()->id,
            'clinic_id'  => $clinic->id,
            'starts_at'  => CarbonImmutable::parse('+2 days 09:00:00'),
            'ends_at'    => CarbonImmutable::parse('+2 days 10:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        postJson(url('/api/appointments'), [
            'doctor_id' => $doctor->id,
            'clinic_id' => $clinic->id,
            'starts_at' => CarbonImmutable::parse('+2 days 10:00:00')->toIso8601String(),
            'ends_at'   => CarbonImmutable::parse('+2 days 11:00:00')->toIso8601String(),
        ])->assertCreated();
    });
});
