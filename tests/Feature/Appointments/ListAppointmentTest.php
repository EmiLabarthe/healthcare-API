<?php

declare(strict_types=1);

namespace Tests\Feature\Appointments;

use Carbon\CarbonImmutable;
use Database\Factories\AppointmentFactory;
use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Database\Factories\PatientFactory;
use Lightit\Appointments\App\Controllers\ListAppointmentController;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Tests\TestCase;

use function Pest\Laravel\getJson;

describe('appointments', function (): void {
    /** @see ListAppointmentController */
    it('lists only appointments belonging to the authenticated patient', function (): void {
        $patient = PatientFactory::new()->createOne();
        $otherPatient = PatientFactory::new()->createOne();

        AppointmentFactory::new()->count(3)->create(['patient_id' => $patient->id]);
        AppointmentFactory::new()->count(2)->create(['patient_id' => $otherPatient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url('/api/appointments'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'doctor_id', 'patient_id', 'clinic_id', 'starts_at', 'ends_at', 'status'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    });

    it('returns an empty data array when the patient has no appointments', function (): void {
        $patient = PatientFactory::new()->createOne();
        AppointmentFactory::new()->count(2)->create();

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url('/api/appointments'))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('orders appointments by starts_at desc by default', function (): void {
        $patient = PatientFactory::new()->createOne();
        $early = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+1 day 09:00:00'),
            'ends_at'    => CarbonImmutable::parse('+1 day 10:00:00'),
        ]);
        $middle = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+5 days 09:00:00'),
            'ends_at'    => CarbonImmutable::parse('+5 days 10:00:00'),
        ]);
        $late = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+10 days 09:00:00'),
            'ends_at'    => CarbonImmutable::parse('+10 days 10:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url('/api/appointments'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $late->id)
            ->assertJsonPath('data.1.id', $middle->id)
            ->assertJsonPath('data.2.id', $early->id);
    });

    it('filters by doctor_id', function (): void {
        $patient = PatientFactory::new()->createOne();
        $targetDoctor = DoctorFactory::new()->createOne();
        $otherDoctor = DoctorFactory::new()->createOne();

        AppointmentFactory::new()->createOne(['patient_id' => $patient->id, 'doctor_id' => $targetDoctor->id]);
        AppointmentFactory::new()->createOne(['patient_id' => $patient->id, 'doctor_id' => $otherDoctor->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url("/api/appointments?filter[doctor_id]={$targetDoctor->id}"))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.doctor_id', $targetDoctor->id);
    });

    it('filters by clinic_id', function (): void {
        $patient = PatientFactory::new()->createOne();
        $targetClinic = ClinicFactory::new()->createOne();
        $otherClinic = ClinicFactory::new()->createOne();

        AppointmentFactory::new()->createOne(['patient_id' => $patient->id, 'clinic_id' => $targetClinic->id]);
        AppointmentFactory::new()->createOne(['patient_id' => $patient->id, 'clinic_id' => $otherClinic->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url("/api/appointments?filter[clinic_id]={$targetClinic->id}"))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.clinic_id', $targetClinic->id);
    });

    it('filters by status', function (): void {
        $patient = PatientFactory::new()->createOne();

        AppointmentFactory::new()->createOne(['patient_id' => $patient->id, 'status' => AppointmentStatus::Scheduled]);
        AppointmentFactory::new()->cancelled()->createOne(['patient_id' => $patient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url('/api/appointments?filter[status]=cancelled'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', AppointmentStatus::Cancelled->value);
    });

    it('sorts by starts_at ascending when requested', function (): void {
        $patient = PatientFactory::new()->createOne();
        $first = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+1 day 09:00:00'),
            'ends_at'    => CarbonImmutable::parse('+1 day 10:00:00'),
        ]);
        $second = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+5 days 09:00:00'),
            'ends_at'    => CarbonImmutable::parse('+5 days 10:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url('/api/appointments?sort=starts_at'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $first->id)
            ->assertJsonPath('data.1.id', $second->id);
    });

    it('sorts by ends_at descending when requested', function (): void {
        $patient = PatientFactory::new()->createOne();
        $first = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+1 day 09:00:00'),
            'ends_at'    => CarbonImmutable::parse('+1 day 10:00:00'),
        ]);
        $second = AppointmentFactory::new()->createOne([
            'patient_id' => $patient->id,
            'starts_at'  => CarbonImmutable::parse('+5 days 09:00:00'),
            'ends_at'    => CarbonImmutable::parse('+5 days 10:00:00'),
        ]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url('/api/appointments?sort=-ends_at'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $second->id)
            ->assertJsonPath('data.1.id', $first->id);
    });

    it('excludes soft-deleted appointments', function (): void {
        $patient = PatientFactory::new()->createOne();
        AppointmentFactory::new()->count(2)->create(['patient_id' => $patient->id]);
        $deleted = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);
        $deleted->delete();

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        getJson(url('/api/appointments'))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('returns 401 when unauthenticated', function (): void {
        getJson(url('/api/appointments'))->assertUnauthorized();
    });
});
