<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Resources;

use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Lightit\Clinics\Domain\Models\Clinic;

/**
 * @mixin Clinic
 */
#[SchemaName('Clinic')]
class ClinicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
        ];
    }
}
