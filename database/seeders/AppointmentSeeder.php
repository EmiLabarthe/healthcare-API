<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Factories\AppointmentFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Lightit\Doctors\Domain\Models\Doctor;
use Lightit\Patients\Domain\Models\Patient;

class AppointmentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $doctors = Doctor::with('clinics')->get();
        $patients = Patient::all();

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
