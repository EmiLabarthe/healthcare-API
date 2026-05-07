<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Lightit\Appointments\Domain\Models\Appointment;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+1 day', '+1 month');
        $endsAt = (clone $startsAt)->modify('+1 hour');

        return [
            'doctor_id' => DoctorFactory::new(),
            'patient_id' => PatientFactory::new(),
            'clinic_id' => ClinicFactory::new(),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => AppointmentStatus::Scheduled,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => AppointmentStatus::Cancelled,
            'deleted_at' => now(),
        ]);
    }
}
