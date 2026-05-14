<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\DoctorFactory;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Doctors\App\Controllers\StoreDoctorController;
use Lightit\Doctors\App\Resources\DoctorResource;
use Lightit\Doctors\Domain\Models\Doctor;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

dataset('store-doctor-validation', [
    'name is required'    => ['name', ''],
    'name must be string' => ['name', ['array']],
    'name too short'      => ['name', 'abc'],
    'name too long'       => ['name', fn (): string => Str::repeat('a', 81)],
]);

describe('doctors', function (): void {
    /** @see StoreDoctorController */
    it('creates a doctor successfully', function (): void {
        $payload = [
            'name' => 'Dr. Gregory House',
        ];

        $response = postJson(url('/api/doctors'), $payload);

        $doctor = Doctor::query()->where('name', $payload['name'])->firstOrFail();

        $response
            ->assertCreated()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        DoctorResource::make($doctor)->resolve()
                    )
                )
            );

        assertDatabaseHas(Doctor::class, [
            'name' => $payload['name'],
        ]);
    });

    it('rejects invalid payloads', function (string $field, string|array $value): void {
        $payload = [
            'name' => 'Valid Doctor Name',
        ];

        postJson(url('/api/doctors'), [...$payload, $field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'error.fields');

        assertDatabaseMissing(Doctor::class, ['name' => 'Valid Doctor Name']);
    })->with('store-doctor-validation');

    it('rejects a missing name field entirely', function (): void {
        postJson(url('/api/doctors'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'], 'error.fields');
    });

    it('allows two doctors to share the same name', function (): void {
        DoctorFactory::new()->createOne(['name' => 'Dr. Strange']);

        postJson(url('/api/doctors'), ['name' => 'Dr. Strange'])
            ->assertCreated();

        expect(Doctor::query()->where('name', 'Dr. Strange')->count())->toBe(2);
    });
});
