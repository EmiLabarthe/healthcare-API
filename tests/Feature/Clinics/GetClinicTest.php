<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Clinics\App\Controllers\GetClinicController;
use Lightit\Clinics\App\Resources\ClinicResource;

use function Pest\Laravel\getJson;

describe('clinics', function (): void {
    /** @see GetClinicController */
    it('retrieves a clinic by id', function (): void {
        $clinic = ClinicFactory::new()->createOne();

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

    it('returns 404 when the clinic does not exist', function (): void {
        getJson(url('/api/clinics/99999'))->assertNotFound();
    });
});
