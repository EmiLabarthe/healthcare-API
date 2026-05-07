<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Factories\AppointmentFactory;
use Database\Factories\ClinicFactory;
use Database\Factories\DoctorFactory;
use Database\Factories\PatientFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        UserFactory::new()->createMany(10);

        $doctors = DoctorFactory::new()->createMany(10);
        $clinics = ClinicFactory::new()->createMany(5);
        $patients = PatientFactory::new()->createMany(20);

        // Assign each doctor to 1-3 random clinics
        $doctors->each(function ($doctor) use ($clinics): void {
            $doctor->clinics()->attach(
                $clinics->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        // Create appointments: each patient gets 1-3 appointments
        $patients->each(function ($patient) use ($doctors): void {
            $count = rand(1, 3);

            for ($i = 0; $i < $count; $i++) {
                $doctor = $doctors->random();
                $clinic = $doctor->clinics->random();

                $startsAt = now()->addDays(rand(1, 60))->addHours(rand(8, 16));

                AppointmentFactory::new()->create([
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id,
                    'clinic_id' => $clinic->id,
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->copy()->addHour(),
                    'status' => AppointmentStatus::Scheduled,
                ]);
            }
        });
    }
}
