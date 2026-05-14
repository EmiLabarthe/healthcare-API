<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\PatientFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Patients\App\Controllers\StorePatientController;
use Lightit\Patients\App\Resources\PatientResource;
use Lightit\Patients\Domain\Models\Patient;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

dataset('store-patient-validation', [
    'name is required'        => ['name', ''],
    'name must be string'     => ['name', ['array']],
    'name too short'          => ['name', 'abc'],
    'name too long'           => ['name', fn (): string => Str::repeat('a', 81)],
    'email is required'       => ['email', ''],
    'email not a valid email' => ['email', 'not-an-email'],
    'email too long'          => ['email', fn (): string => Str::repeat('a', 96) . '@e.io'],
    'password is required'    => ['password', ''],
    'password too short'      => ['password', 'A1#bc'],
    'password no uppercase'   => ['password', 'lowercase1#'],
    'password no lowercase'   => ['password', 'UPPERCASE1#'],
    'password no numbers'     => ['password', 'NoNumbers#'],
    'password no symbols'     => ['password', 'NoSymbols1'],
]);

describe('patients', function (): void {
    /** @see StorePatientController */
    it('creates a patient successfully', function (): void {
        $payload = [
            'name'     => 'Jane Doe',
            'email'    => 'jane@example.com',
            'password' => 'Str0ng#Pass1',
        ];

        $response = postJson(url('/api/patients'), $payload);

        $patient = Patient::query()->where('email', $payload['email'])->firstOrFail();

        $response
            ->assertCreated()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        PatientResource::make($patient)->resolve()
                    )
                )
            );

        assertDatabaseHas('patients', [
            'name'  => $payload['name'],
            'email' => $payload['email'],
        ]);
    });

    it('lowercases the email on store', function (): void {
        postJson(url('/api/patients'), [
            'name'     => 'Mixed Case',
            'email'    => 'MixedCase@Example.COM',
            'password' => 'Str0ng#Pass1',
        ])->assertCreated();

        assertDatabaseHas('patients', ['email' => 'mixedcase@example.com']);
        assertDatabaseMissing('patients', ['email' => 'MixedCase@Example.COM']);
    });

    it('hashes the password on store', function (): void {
        postJson(url('/api/patients'), [
            'name'     => 'Plain Pass',
            'email'    => 'plain@example.com',
            'password' => 'Str0ng#Pass1',
        ])->assertCreated();

        $patient = Patient::query()->where('email', 'plain@example.com')->firstOrFail();

        expect($patient->password)
            ->not->toBe('Str0ng#Pass1')
            ->and(Hash::check('Str0ng#Pass1', (string) $patient->password))
            ->toBeTrue();
    });

    it('does not expose password or remember_token in the response', function (): void {
        postJson(url('/api/patients'), [
            'name'     => 'Hidden Fields',
            'email'    => 'hidden@example.com',
            'password' => 'Str0ng#Pass1',
        ])
            ->assertCreated()
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');
    });

    it('rejects duplicate emails', function (): void {
        PatientFactory::new()->createOne(['email' => 'taken@example.com']);

        postJson(url('/api/patients'), [
            'name'     => 'Some Patient',
            'email'    => 'taken@example.com',
            'password' => 'Str0ng#Pass1',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email'], 'error.fields');
    });

    it('rejects invalid payloads', function (string $field, string|array $value): void {
        $payload = [
            'name'     => 'Valid Name',
            'email'    => 'valid@example.com',
            'password' => 'Str0ng#Pass1',
        ];

        postJson(url('/api/patients'), [...$payload, $field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'error.fields');

        assertDatabaseMissing('patients', ['email' => 'valid@example.com']);
    })->with('store-patient-validation');
});
