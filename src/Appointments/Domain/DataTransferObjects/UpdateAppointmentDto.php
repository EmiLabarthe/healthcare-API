<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\DataTransferObjects;

use Carbon\CarbonImmutable;

readonly class UpdateAppointmentDto
{
    public function __construct(
        public ?int $doctorId = null,
        public ?int $patientId = null,
        public ?int $clinicId = null,
        public ?CarbonImmutable $startsAt = null,
        public ?CarbonImmutable $endsAt = null,
    ) {
    }
}
