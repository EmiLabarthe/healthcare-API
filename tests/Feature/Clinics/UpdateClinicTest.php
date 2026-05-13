<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Clinics\App\Controllers\UpdateClinicController;
use Lightit\Clinics\App\Resources\ClinicResource;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

describe('clinics', function (): void {
    /** @see UpdateClinicController */
    it('updates both fields of a clinic', function (): void {
        $clinic = ClinicFactory::new()->createOne([
            'name'    => 'Old Name',
            'address' => 'Old Address',
        ]);

        $payload = [
            'name'    => 'New Name',
            'address' => 'New Address 42',
        ];

        $response = patchJson(url("/api/clinics/{$clinic->id}"), $payload);

        $clinic->refresh();

        $response
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        ClinicResource::make($clinic)->resolve()
                    )
                )
            );

        assertDatabaseHas('clinics', [
            'id'      => $clinic->id,
            'name'    => 'New Name',
            'address' => 'New Address 42',
        ]);
    });

    it('updates only the provided field', function (): void {
        $clinic = ClinicFactory::new()->createOne([
            'name'    => 'Original',
            'address' => 'Keep Me',
        ]);

        patchJson(url("/api/clinics/{$clinic->id}"), ['name' => 'Renamed'])
            ->assertOk();

        assertDatabaseHas('clinics', [
            'id'      => $clinic->id,
            'name'    => 'Renamed',
            'address' => 'Keep Me',
        ]);
    });

    it('accepts an empty payload as a no-op', function (): void {
        $clinic = ClinicFactory::new()->createOne([
            'name'    => 'Unchanged',
            'address' => 'Unchanged Address',
        ]);

        patchJson(url("/api/clinics/{$clinic->id}"), [])
            ->assertOk();

        assertDatabaseHas('clinics', [
            'id'      => $clinic->id,
            'name'    => 'Unchanged',
            'address' => 'Unchanged Address',
        ]);
    });

    it('returns 404 when updating a missing clinic', function (): void {
        patchJson(url('/api/clinics/99999'), ['name' => 'Anything Goes'])
            ->assertNotFound();
    });

    it('rejects invalid update payloads', function (string $field, string|array $value): void {
        $clinic = ClinicFactory::new()->createOne();

        patchJson(url("/api/clinics/{$clinic->id}"), [$field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'error.fields');
    })->with([
        'name too short'    => ['name', 'abc'],
        'name too long'     => ['name', fn (): string => Str::repeat('a', 81)],
        'name not a string' => ['name', ['array']],
        'address too long'  => ['address', fn (): string => Str::repeat('a', 256)],
    ]);
});
