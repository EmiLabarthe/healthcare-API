<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\PatientFactory;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Patients\App\Controllers\UpdatePatientController;
use Lightit\Patients\App\Resources\PatientResource;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

describe('patients', function (): void {
    /** @see UpdatePatientController */
    it('updates both name and email of a patient', function (): void {
        $patient = PatientFactory::new()->createOne([
            'name'  => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $payload = [
            'name'  => 'New Name',
            'email' => 'new@example.com',
        ];

        $response = patchJson(url("/api/patients/{$patient->id}"), $payload);

        $patient->refresh();

        $response
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        PatientResource::make($patient)->resolve()
                    )
                )
            );

        assertDatabaseHas('patients', [
            'id'    => $patient->id,
            'name'  => 'New Name',
            'email' => 'new@example.com',
        ]);
    });

    it('updates only the provided field', function (): void {
        $patient = PatientFactory::new()->createOne([
            'name'  => 'Original',
            'email' => 'keep@example.com',
        ]);

        patchJson(url("/api/patients/{$patient->id}"), ['name' => 'Renamed'])
            ->assertOk();

        assertDatabaseHas('patients', [
            'id'    => $patient->id,
            'name'  => 'Renamed',
            'email' => 'keep@example.com',
        ]);
    });

    it('accepts an empty payload as a no-op', function (): void {
        $patient = PatientFactory::new()->createOne([
            'name'  => 'Unchanged',
            'email' => 'unchanged@example.com',
        ]);

        patchJson(url("/api/patients/{$patient->id}"), [])
            ->assertOk();

        assertDatabaseHas('patients', [
            'id'    => $patient->id,
            'name'  => 'Unchanged',
            'email' => 'unchanged@example.com',
        ]);
    });

    it('lowercases the email on update', function (): void {
        $patient = PatientFactory::new()->createOne(['email' => 'before@example.com']);

        patchJson(url("/api/patients/{$patient->id}"), ['email' => 'AfterMix@Example.COM'])
            ->assertOk();

        assertDatabaseHas('patients', [
            'id'    => $patient->id,
            'email' => 'aftermix@example.com',
        ]);
    });

    it('allows keeping the same email on update (unique-ignore-self)', function (): void {
        $patient = PatientFactory::new()->createOne([
            'name'  => 'Keep Email',
            'email' => 'sameemail@example.com',
        ]);

        patchJson(url("/api/patients/{$patient->id}"), [
            'name'  => 'New Display Name',
            'email' => 'sameemail@example.com',
        ])->assertOk();

        assertDatabaseHas('patients', [
            'id'    => $patient->id,
            'name'  => 'New Display Name',
            'email' => 'sameemail@example.com',
        ]);
    });

    it('allows keeping the same email when submitted with different casing', function (): void {
        $patient = PatientFactory::new()->createOne([
            'name'  => 'Case Keeper',
            'email' => 'self@example.com',
        ]);

        patchJson(url("/api/patients/{$patient->id}"), [
            'email' => 'SELF@EXAMPLE.COM',
        ])->assertOk();

        assertDatabaseHas('patients', [
            'id'    => $patient->id,
            'name'  => 'Case Keeper',
            'email' => 'self@example.com',
        ]);
    });

    it('rejects an email already in use by a different patient', function (): void {
        PatientFactory::new()->createOne(['email' => 'occupied@example.com']);
        $patient = PatientFactory::new()->createOne(['email' => 'mine@example.com']);

        patchJson(url("/api/patients/{$patient->id}"), ['email' => 'occupied@example.com'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'], 'error.fields');
    });

    it('returns 404 when updating a missing patient', function (): void {
        patchJson(url('/api/patients/99999'), ['name' => 'Anything Goes'])
            ->assertNotFound();
    });

    it('returns 404 for a non-numeric patient id', function (): void {
        patchJson(url('/api/patients/not-a-number'), ['name' => 'Anything'])
            ->assertNotFound();
    });

    it('rejects invalid update payloads', function (string $field, string|array $value): void {
        $patient = PatientFactory::new()->createOne();

        patchJson(url("/api/patients/{$patient->id}"), [$field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'error.fields');
    })->with([
        'name too short'        => ['name', 'abc'],
        'name too long'         => ['name', fn (): string => Str::repeat('a', 81)],
        'name not a string'     => ['name', ['array']],
        'email not a valid one' => ['email', 'not-an-email'],
        'email too long'        => ['email', fn (): string => Str::repeat('a', 96) . '@e.io'],
    ]);
});
