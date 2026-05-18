<?php

declare(strict_types=1);

namespace Tests\Feature\Doctors;

use Database\Factories\DoctorFactory;
use Lightit\Doctors\App\Controllers\ListDoctorController;

use function Pest\Laravel\getJson;

describe('doctors', function (): void {
    /** @see ListDoctorController */
    it('lists doctors successfully', function (): void {
        DoctorFactory::new()->count(5)->create();

        getJson(url('/api/doctors'))
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 5)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'clinics'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    });

    it('returns an empty data array when no doctors exist', function (): void {
        getJson(url('/api/doctors'))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    });

    it('filters doctors by name', function (): void {
        DoctorFactory::new()->createOne(['name' => 'Dr. House']);
        DoctorFactory::new()->createOne(['name' => 'Dr. Wilson']);

        getJson(url('/api/doctors?filter[name]=House'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Dr. House');
    });

    it('sorts doctors by name ascending', function (): void {
        DoctorFactory::new()->createOne(['name' => 'Alice']);
        DoctorFactory::new()->createOne(['name' => 'Mallory']);
        DoctorFactory::new()->createOne(['name' => 'Zara']);

        getJson(url('/api/doctors?sort=name'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Alice')
            ->assertJsonPath('data.1.name', 'Mallory')
            ->assertJsonPath('data.2.name', 'Zara');
    });

    it('orders doctors by id desc by default', function (): void {
        $first = DoctorFactory::new()->createOne(['name' => 'First Created']);
        $second = DoctorFactory::new()->createOne(['name' => 'Second Created']);
        $third = DoctorFactory::new()->createOne(['name' => 'Third Created']);

        getJson(url('/api/doctors'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $third->id)
            ->assertJsonPath('data.1.id', $second->id)
            ->assertJsonPath('data.2.id', $first->id);
    });
});
