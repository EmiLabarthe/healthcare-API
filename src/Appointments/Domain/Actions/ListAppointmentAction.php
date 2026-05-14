<?php

declare(strict_types=1);

namespace Lightit\Appointments\Domain\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Lightit\Appointments\Domain\Models\Appointment;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ListAppointmentAction
{
    /**
     * @return LengthAwarePaginator<int, Appointment>
     */
    public function execute(): LengthAwarePaginator
    {
        $baseQuery = Appointment::query()->where('patient_id', (int) Auth::id());

        return QueryBuilder::for($baseQuery)
            ->allowedFilters([
                AllowedFilter::exact('doctor_id'),
                AllowedFilter::exact('clinic_id'),
                AllowedFilter::exact('status'),
            ])
            ->allowedSorts(['starts_at', 'ends_at'])
            ->orderBy('starts_at', 'desc')
            ->paginate();
    }
}
