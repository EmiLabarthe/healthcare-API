<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\PatientFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Patients\App\Controllers\GetPatientController;
use Lightit\Patients\App\Resources\PatientResource;

use function Pest\Laravel\getJson;

describe('patients', function (): void {
    /** @see GetPatientController */
    it('retrieves a patient by id', function (): void {
        $patient = PatientFactory::new()->createOne();

        getJson(url("/api/patients/{$patient->id}"))
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        PatientResource::make($patient)->resolve()
                    )
                )
            );
    });

    it('does not expose password or remember_token', function (): void {
        $patient = PatientFactory::new()->createOne();

        getJson(url("/api/patients/{$patient->id}"))
            ->assertOk()
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');
    });

    it('returns 404 when the patient does not exist', function (): void {
        getJson(url('/api/patients/99999'))->assertNotFound();
    });
});
