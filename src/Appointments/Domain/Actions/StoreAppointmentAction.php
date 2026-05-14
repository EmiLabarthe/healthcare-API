<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\Actions;

use Lightit\Appointments\Domain\DataTransferObjects\StoreAppointmentDto;
use Lightit\Appointments\Domain\Enums\AppointmentStatus;
use Lightit\Appointments\Domain\Models\Appointment;

class StoreAppointmentAction
{
    public function execute(StoreAppointmentDto $dto): Appointment
    {
        $appointment = new Appointment();

        $appointment->doctor_id = $dto->doctorId;
        $appointment->patient_id = $dto->patientId;
        $appointment->clinic_id = $dto->clinicId;
        $appointment->starts_at = $dto->startsAt;
        $appointment->ends_at = $dto->endsAt;
        $appointment->status = AppointmentStatus::Scheduled;

        $appointment->saveOrFail();

        return $appointment;
    }
}
