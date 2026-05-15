<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\DoctorFactory;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Doctors\App\Controllers\UpdateDoctorController;
use Lightit\Doctors\App\Resources\DoctorResource;
use Lightit\Doctors\Domain\Models\Doctor;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;

dataset('update-doctor-validation', [
    'name too short'    => ['name', 'abc'],
    'name too long'     => ['name', fn (): string => Str::repeat('a', 81)],
    'name not a string' => ['name', ['array']],
]);

describe('doctors', function (): void {
    /** @see UpdateDoctorController */
    it('updates a doctor name', function (): void {
        $doctor = DoctorFactory::new()->createOne(['name' => 'Old Name']);

        $payload = ['name' => 'New Name'];

        $response = patchJson(url("/api/doctors/{$doctor->id}"), $payload);

        $doctor->refresh();

        $response
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        DoctorResource::make($doctor)->resolve()
                    )
                )
            );

        assertDatabaseHas(Doctor::class, [
            'id'   => $doctor->id,
            'name' => 'New Name',
        ]);
    });

    it('accepts an empty payload as a no-op', function (): void {
        $doctor = DoctorFactory::new()->createOne(['name' => 'Unchanged']);

        patchJson(url("/api/doctors/{$doctor->id}"), [])
            ->assertOk();

        assertDatabaseHas(Doctor::class, [
            'id'   => $doctor->id,
            'name' => 'Unchanged',
        ]);
    });

    it('returns 404 when updating a missing doctor', function (): void {
        patchJson(url('/api/doctors/99999'), ['name' => 'Anything Goes'])
            ->assertNotFound();
    });

    it('rejects invalid update payloads', function (string $field, string|array $value): void {
        $doctor = DoctorFactory::new()->createOne();

        patchJson(url("/api/doctors/{$doctor->id}"), [$field => $value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field], 'error.fields');
    })->with('update-doctor-validation');
});
