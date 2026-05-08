<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\DataTransferObjects;

use Carbon\CarbonImmutable;

readonly class UpdateAppointmentDto
{
    public function __construct(
        public int|null $doctorId = null,
        public CarbonImmutable|null $startsAt = null,
        public CarbonImmutable|null $endsAt = null,
    ) {
    }
}
