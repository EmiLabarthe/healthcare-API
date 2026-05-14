<?php

declare(strict_types=1);

namespace Tests\Unit\Appointments;

use Database\Factories\AppointmentFactory;
use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Database\Factories\PatientFactory;
use Lightit\Appointments\Domain\Models\Appointment;
use Lightit\Clinics\Domain\Models\Clinic;
use Lightit\Doctors\Domain\Models\Doctor;
use Lightit\Patients\Domain\Models\Patient;
use Tests\TestCase;

describe('Appointment::resolveRouteBinding', function (): void {
    it('returns null when no api user is authenticated', function (): void {
        $appointment = AppointmentFactory::new()->createOne();

        $resolved = new Appointment()->resolveRouteBinding($appointment->id);

        expect($resolved)->toBeNull();
    });

    it('resolves an appointment that belongs to the authenticated patient', function (): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        /** @var TestCase $this */
        $this->actingAs($patient, 'api');

        /** @var Appointment|null $resolved */
        $resolved = new Appointment()->resolveRouteBinding($appointment->id);

        expect($resolved?->id)->toBe($appointment->id);
    });

    it('throws ModelNotFoundException for an appointment that belongs to another patient', function (): void {
        $owner = PatientFactory::new()->createOne();
        $intruder = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $owner->id]);

        /** @var TestCase $this */
        $this->actingAs($intruder, 'api');

        expect(
            fn (): \Illuminate\Database\Eloquent\Model|null => new Appointment()->resolveRouteBinding($appointment->id)
        )
            ->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    });
});

describe('Appointment relationships', function (): void {
    it('returns the associated doctor via the doctor() relation', function (): void {
        $doctor = DoctorFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['doctor_id' => $doctor->id]);

        expect($appointment->doctor)->toBeInstanceOf(Doctor::class)
            ->and($appointment->doctor->id)->toBe($doctor->id);
    });

    it('returns the associated patient via the patient() relation', function (): void {
        $patient = PatientFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['patient_id' => $patient->id]);

        expect($appointment->patient)->toBeInstanceOf(Patient::class)
            ->and($appointment->patient->id)->toBe($patient->id);
    });

    it('returns the associated clinic via the clinic() relation', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $appointment = AppointmentFactory::new()->createOne(['clinic_id' => $clinic->id]);

        expect($appointment->clinic)->toBeInstanceOf(Clinic::class)
            ->and($appointment->clinic->id)->toBe($clinic->id);
    });
});
