<?php

declare(strict_types=1);

namespace Tests\Unit\Patients;

use Carbon\CarbonImmutable;
use Database\Factories\AppointmentFactory;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Lightit\Appointments\Domain\Models\Appointment;
use Lightit\Patients\Domain\Models\Patient;

describe('Patient::email accessor and mutator', function (): void {
    it('lowercases the email when set via mass-assignment-style write', function (): void {
        $patient = PatientFactory::new()->createOne(['email' => 'Mixed@Example.COM']);

        expect($patient->email)->toBe('mixed@example.com');
    });

    it('lowercases the email when set via direct attribute assignment', function (): void {
        $patient = PatientFactory::new()->createOne();

        $patient->email = 'NEW.Address@Example.com';
        $patient->save();

        $patient->refresh();

        expect($patient->email)->toBe('new.address@example.com');
    });

    it('persists the email lowercased to the database', function (): void {
        $patient = PatientFactory::new()->createOne(['email' => 'StoredCase@Example.COM']);

        /** @var Patient $fromDb */
        $fromDb = Patient::query()->whereKey($patient->id)->firstOrFail();

        expect($fromDb->getRawOriginal('email'))->toBe('storedcase@example.com');
    });
});

describe('Patient::password cast', function (): void {
    it('hashes the password automatically when assigned plain text', function (): void {
        $patient = PatientFactory::new()->createOne();

        $patient->password = 'Str0ng#Pass1';
        $patient->save();

        $patient->refresh();

        expect($patient->password)
            ->not->toBe('Str0ng#Pass1')
            ->and(Hash::check('Str0ng#Pass1', (string) $patient->password))
            ->toBeTrue();
    });
});

describe('Patient::email_verified_at cast', function (): void {
    it('returns a CarbonImmutable when set', function (): void {
        $patient = PatientFactory::new()->createOne();

        $patient->email_verified_at = CarbonImmutable::parse('2026-01-15 10:30:00');
        $patient->save();

        $patient->refresh();

        expect($patient->email_verified_at)
            ->toBeInstanceOf(CarbonImmutable::class)
            ->and($patient->email_verified_at?->toDateTimeString())
            ->toBe('2026-01-15 10:30:00');
    });

    it('is null by default', function (): void {
        $patient = PatientFactory::new()->createOne();

        expect($patient->email_verified_at)->toBeNull();
    });
});

describe('Patient hidden attributes', function (): void {
    it('hides password from array conversion', function (): void {
        $patient = PatientFactory::new()->createOne();

        expect($patient->toArray())
            ->not->toHaveKey('password')
            ->and($patient->toArray())->not->toHaveKey('remember_token');
    });

    it('hides password from JSON serialization', function (): void {
        $patient = PatientFactory::new()->createOne();

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) json_encode($patient), true);

        expect($decoded)
            ->not->toHaveKey('password')
            ->and($decoded)->not->toHaveKey('remember_token');
    });
});

describe('Patient::appointments relationship', function (): void {
    it('returns a HasMany relation instance', function (): void {
        $patient = PatientFactory::new()->createOne();

        expect($patient->appointments())->toBeInstanceOf(HasMany::class);
    });

    it('returns only the appointments that belong to the patient', function (): void {
        $owner = PatientFactory::new()->createOne();
        $stranger = PatientFactory::new()->createOne();

        $ownAppointment = AppointmentFactory::new()->createOne(['patient_id' => $owner->id]);
        AppointmentFactory::new()->createOne(['patient_id' => $stranger->id]);

        $appointments = $owner->appointments()->get();

        expect($appointments)->toHaveCount(1)
            ->and($appointments->first())->toBeInstanceOf(Appointment::class)
            ->and($appointments->first()?->id)->toBe($ownAppointment->id);
    });

    it('returns an empty collection when the patient has no appointments', function (): void {
        $patient = PatientFactory::new()->createOne();

        expect($patient->appointments()->get())->toHaveCount(0);
    });
});
