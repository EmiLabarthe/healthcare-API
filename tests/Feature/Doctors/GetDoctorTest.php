<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\DoctorFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Doctors\App\Controllers\GetDoctorController;
use Lightit\Doctors\App\Resources\DoctorResource;

use function Pest\Laravel\getJson;

describe('doctors', function (): void {
    /** @see GetDoctorController */
    it('retrieves a doctor by id', function (): void {
        $doctor = DoctorFactory::new()->createOne();

        getJson(url("/api/doctors/{$doctor->id}"))
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json): AssertableJson => $json->has(
                    'data',
                    fn (AssertableJson $json): AssertableJson => $json->whereAll(
                        DoctorResource::make($doctor)->resolve()
                    )
                )
            );
    });

    it('returns 404 when the doctor does not exist', function (): void {
        getJson(url('/api/doctors/99999'))->assertNotFound();
    });
});
