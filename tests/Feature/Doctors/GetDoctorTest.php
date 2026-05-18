<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Lightit\Doctors\App\Controllers\GetDoctorController;

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
                    fn (AssertableJson $json): AssertableJson => $json
                        ->where('id', $doctor->id)
                        ->where('name', $doctor->name)
                        ->has('clinics')
                )
            );
    });

    it('includes clinics the doctor works at', function (): void {
        $clinics = ClinicFactory::new()->count(2)->create();
        $doctor = DoctorFactory::new()->createOne();
        $doctor->clinics()->attach($clinics->pluck('id'));

        getJson(url("/api/doctors/{$doctor->id}"))
            ->assertOk()
            ->assertJsonCount(2, 'data.clinics')
            ->assertJsonStructure([
                'data' => [
                    'clinics' => [
                        '*' => ['id', 'name', 'address'],
                    ],
                ],
            ]);
    });

    it('returns 404 when the doctor does not exist', function (): void {
        getJson(url('/api/doctors/99999'))->assertNotFound();
    });
});
