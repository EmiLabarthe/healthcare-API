<?php

declare(strict_types=1);

namespace Tests\Feature\Clinics;

use Database\Factories\ClinicFactory;
use Lightit\Clinics\App\Controllers\ListClinicController;

use function Pest\Laravel\getJson;

describe('clinics', function (): void {
    /** @see ListClinicController */
    it('lists clinics successfully', function (): void {
        ClinicFactory::new()->count(5)->create();

        getJson(url('/api/clinics'))
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 5)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'address'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    });

    it('returns an empty data array when no clinics exist', function (): void {
        getJson(url('/api/clinics'))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('filters clinics by name', function (): void {
        ClinicFactory::new()->createOne(['name' => 'St. Mary Clinic']);
        ClinicFactory::new()->createOne(['name' => 'Downtown Health']);

        getJson(url('/api/clinics?filter[name]=Mary'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'St. Mary Clinic');
    });

    it('sorts clinics by name ascending', function (): void {
        ClinicFactory::new()->createOne(['name' => 'Alpha Clinic']);
        ClinicFactory::new()->createOne(['name' => 'Mellow Clinic']);
        ClinicFactory::new()->createOne(['name' => 'Zeta Clinic']);

        getJson(url('/api/clinics?sort=name'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alpha Clinic')
            ->assertJsonPath('data.1.name', 'Mellow Clinic')
            ->assertJsonPath('data.2.name', 'Zeta Clinic');
    });
});
