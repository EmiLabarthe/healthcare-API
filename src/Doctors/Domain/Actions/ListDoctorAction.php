<?php

declare(strict_types=1);

namespace Lightit\Doctors\Domain\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Lightit\Doctors\Domain\Models\Doctor;
use Spatie\QueryBuilder\QueryBuilder;

class ListDoctorAction
{
    /**
     * @return LengthAwarePaginator<int, Doctor>
     */
    public function execute(): LengthAwarePaginator
    {
        return QueryBuilder::for(Doctor::class)
            ->allowedFilters(['name'])
            ->allowedSorts('name')
            ->orderBy('id', 'desc')
            ->paginate();
    }
}
