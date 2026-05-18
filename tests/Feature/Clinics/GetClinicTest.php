<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Clinics\App\Controllers\GetClinicController;
use Lightit\Clinics\App\Resources\ClinicResource;

use function Pest\Laravel\getJson;

describe('clinics', function (): void {
    /** @see GetClinicController */
    it('retrieves a clinic by id', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $clinic->loadCount('doctors');

        getJson(url("/api/clinics/{$clinic->id}"))
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        ClinicResource::make($clinic)->resolve()
                    )
                )
            );
    });

    it('includes the number of doctors working at the clinic', function (): void {
        $clinic = ClinicFactory::new()->createOne();
        $doctors = DoctorFactory::new()->count(3)->create();
        $clinic->doctors()->attach($doctors->pluck('id'));

        getJson(url("/api/clinics/{$clinic->id}"))
            ->assertOk()
            ->assertJsonPath('data.doctors_count', 3);
    });

    it('returns 404 when the clinic does not exist', function (): void {
        getJson(url('/api/clinics/99999'))->assertNotFound();
    });
});
