<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Clinics\App\Controllers\StoreClinicController;
use Lightit\Clinics\App\Resources\ClinicResource;
use Lightit\Clinics\Domain\Models\Clinic;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;

dataset('store-clinic-validation', [
    'name is required'    => ['name', ''],
    'name must be string' => ['name', ['array']],
    'name too short'      => ['name', 'abc'],
    'name too long'       => ['name', fn (): string => Str::repeat('a', 81)],
    'address is required' => ['address', ''],
    'address too long'    => ['address', fn (): string => Str::repeat('a', 256)],
]);

describe('clinics', function (): void {
    /** @see StoreClinicController */
    it('creates a clinic successfully', function (): void {
        $payload = [
            'name'    => 'Sunrise Medical Center',
            'address' => '123 Health Way, Springfield',
        ];

        $response = postJson(url('/api/clinics'), $payload);

        $clinic = Clinic::query()->where('name', $payload['name'])->firstOrFail();

        $response
            ->assertCreated()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        ClinicResource::make($clinic)->resolve()
                    )
                )
            );

        assertDatabaseHas(Clinic::class, [
            'name'    => $payload['name'],
            'address' => $payload['address'],
        ]);
    });

    it('rejects invalid payloads', function (string $field, string|array $value): void {
        $payload = [
            'name'    => 'Valid Clinic Name',
            'address' => '42 Example Street',
        ];

        postJson(url('/api/clinics'), [...$payload, $field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'error.fields');

        assertDatabaseMissing(Clinic::class, ['name' => 'Valid Clinic Name']);
    })->with('store-clinic-validation');
});
