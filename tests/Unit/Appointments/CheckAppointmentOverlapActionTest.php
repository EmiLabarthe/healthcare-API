<?php

declare(strict_types=1);

namespace Tests\Unit\Appointments;

use Carbon\CarbonImmutable;
use Database\Factories\AppointmentFactory;
use Database\Factories\DoctorFactory;
use Database\Factories\PatientFactory;
use Lightit\Appointments\Domain\Actions\CheckAppointmentOverlapAction;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Lightit\Doctors\Domain\Models\Doctor;
use Lightit\Patients\Domain\Models\Patient;
use Tests\TestCase;

describe('CheckAppointmentOverlapAction', function (): void {
    beforeEach(function (): void {
        /** @var TestCase $this */
        $this->action = resolve(CheckAppointmentOverlapAction::class);
        $this->doctor = DoctorFactory::new()->createOne();
        $this->patient = PatientFactory::new()->createOne();

        $this->existing = AppointmentFactory::new()->createOne([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'starts_at' => CarbonImmutable::parse('2026-06-01 10:00:00'),
            'ends_at' => CarbonImmutable::parse('2026-06-01 11:00:00'),
        ]);
    });

    it('detects an identical-window overlap', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 10:00:00'),
            CarbonImmutable::parse('2026-06-01 11:00:00'),
        );

        expect($result)->toBeTrue();
    });

    it('detects a partial overlap on the left edge', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 09:30:00'),
            CarbonImmutable::parse('2026-06-01 10:30:00'),
        );

        expect($result)->toBeTrue();
    });

    it('detects a partial overlap on the right edge', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 10:30:00'),
            CarbonImmutable::parse('2026-06-01 11:30:00'),
        );

        expect($result)->toBeTrue();
    });

    it('detects a fully-contained overlap', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 10:15:00'),
            CarbonImmutable::parse('2026-06-01 10:45:00'),
        );

        expect($result)->toBeTrue();
    });

    it('detects an encompassing overlap', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 09:00:00'),
            CarbonImmutable::parse('2026-06-01 12:00:00'),
        );

        expect($result)->toBeTrue();
    });

    it('does not flag a window that ends exactly when the existing one starts', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 09:00:00'),
            CarbonImmutable::parse('2026-06-01 10:00:00'),
        );

        expect($result)->toBeFalse();
    });

    it('does not flag a window that starts exactly when the existing one ends', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 11:00:00'),
            CarbonImmutable::parse('2026-06-01 12:00:00'),
        );

        expect($result)->toBeFalse();
    });

    it('does not flag a window with no temporal intersection', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-02 10:00:00'),
            CarbonImmutable::parse('2026-06-02 11:00:00'),
        );

        expect($result)->toBeFalse();
    });

    it('ignores cancelled appointments', function (): void {
        /** @var TestCase $this */
        $this->existing->update(['status' => AppointmentStatus::Cancelled]);

        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 10:00:00'),
            CarbonImmutable::parse('2026-06-01 11:00:00'),
        );

        expect($result)->toBeFalse();
    });

    it('ignores soft-deleted appointments', function (): void {
        /** @var TestCase $this */
        $this->existing->delete();

        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 10:00:00'),
            CarbonImmutable::parse('2026-06-01 11:00:00'),
        );

        expect($result)->toBeFalse();
    });

    it('ignores the appointment specified by excludeAppointmentId', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 10:00:00'),
            CarbonImmutable::parse('2026-06-01 11:00:00'),
            $this->existing->id,
        );

        expect($result)->toBeFalse();
    });

    it('still flags a different overlapping appointment when excludeAppointmentId is set', function (): void {
        /** @var TestCase $this */
        $other = AppointmentFactory::new()->createOne([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'starts_at' => CarbonImmutable::parse('2026-06-01 12:00:00'),
            'ends_at' => CarbonImmutable::parse('2026-06-01 13:00:00'),
        ]);

        $result = $this->action->execute(
            Doctor::class,
            $this->doctor->id,
            CarbonImmutable::parse('2026-06-01 12:30:00'),
            CarbonImmutable::parse('2026-06-01 13:30:00'),
            $this->existing->id,
        );

        expect($result)->toBeTrue();
    });

    it('checks overlap against the Patient parent independently of the Doctor', function (): void {
        /** @var TestCase $this */
        $result = $this->action->execute(
            Patient::class,
            $this->patient->id,
            CarbonImmutable::parse('2026-06-01 10:30:00'),
            CarbonImmutable::parse('2026-06-01 11:30:00'),
        );

        expect($result)->toBeTrue();
    });

    it('returns false when no appointments exist for the given parent', function (): void {
        /** @var TestCase $this */
        $unrelatedDoctor = DoctorFactory::new()->createOne();

        $result = $this->action->execute(
            Doctor::class,
            $unrelatedDoctor->id,
            CarbonImmutable::parse('2026-06-01 10:00:00'),
            CarbonImmutable::parse('2026-06-01 11:00:00'),
        );

        expect($result)->toBeFalse();
    });
});
