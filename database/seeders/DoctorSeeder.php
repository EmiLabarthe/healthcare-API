<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $doctors = DoctorFactory::new()->createMany(10);
        $clinics = ClinicFactory::new()->createMany(5);

        $doctors->each(function ($doctor) use ($clinics): void {
            $doctor->clinics()->attach(
                $clinics->random(rand(1, 3))->pluck('id')->toArray()
            );
        });
    }
}
