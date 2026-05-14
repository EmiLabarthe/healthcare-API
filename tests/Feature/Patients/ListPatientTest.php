<?php

declare(strict_types=1);

namespace Tests\Feature\Patients;

use Database\Factories\PatientFactory;
use Lightit\Patients\App\Controllers\ListPatientController;

use function Pest\Laravel\getJson;

describe('patients', function (): void {
    /** @see ListPatientController */
    it('lists patients successfully', function (): void {
        PatientFactory::new()->count(5)->create();

        getJson(url('/api/patients'))
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 5)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    });

    it('returns an empty data array when no patients exist', function (): void {
        getJson(url('/api/patients'))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('does not expose password or remember_token in any row', function (): void {
        PatientFactory::new()->count(2)->create();

        $response = getJson(url('/api/patients'))->assertOk();

        /** @var array<int, array<string, mixed>> $data */
        $data = $response->json('data');

        foreach ($data as $row) {
            expect($row)
                ->not->toHaveKey('password')
                ->and($row)->not->toHaveKey('remember_token');
        }
    });

    it('orders by id descending by default (newest first)', function (): void {
        $first = PatientFactory::new()->createOne(['name' => 'First Created']);
        $second = PatientFactory::new()->createOne(['name' => 'Second Created']);
        $third = PatientFactory::new()->createOne(['name' => 'Third Created']);

        getJson(url('/api/patients'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $third->id)
            ->assertJsonPath('data.1.id', $second->id)
            ->assertJsonPath('data.2.id', $first->id);
    });

    it('filters patients by name', function (): void {
        PatientFactory::new()->createOne(['name' => 'Alice Wonderland']);
        PatientFactory::new()->createOne(['name' => 'Bob Builder']);

        getJson(url('/api/patients?filter[name]=Alice'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alice Wonderland');
    });

    it('filters patients by email', function (): void {
        PatientFactory::new()->createOne(['email' => 'findme@example.com']);
        PatientFactory::new()->createOne(['email' => 'other@example.com']);

        getJson(url('/api/patients?filter[email]=findme'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'findme@example.com');
    });

    it('sorts patients by name ascending', function (): void {
        PatientFactory::new()->createOne(['name' => 'Alpha Patient']);
        PatientFactory::new()->createOne(['name' => 'Mellow Patient']);
        PatientFactory::new()->createOne(['name' => 'Zeta Patient']);

        getJson(url('/api/patients?sort=name'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alpha Patient')
            ->assertJsonPath('data.1.name', 'Mellow Patient')
            ->assertJsonPath('data.2.name', 'Zeta Patient');
    });
});
