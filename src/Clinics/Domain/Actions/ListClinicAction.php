<?php

declare(strict_types=1);

namespace Lightit\Clinics\Domain\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Lightit\Clinics\Domain\Models\Clinic;
use Spatie\QueryBuilder\QueryBuilder;

class ListClinicAction
{
    /**
     * @return LengthAwarePaginator<int, Clinic>
     */
    public function execute(): LengthAwarePaginator
    {
        return QueryBuilder::for(Clinic::class)
            ->allowedFilters(['name', 'address'])
            ->allowedSorts('name')
            ->orderBy('id', 'desc')
            ->paginate();
    }
}
