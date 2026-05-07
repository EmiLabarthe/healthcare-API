<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Cancelled = 'cancelled';
}
