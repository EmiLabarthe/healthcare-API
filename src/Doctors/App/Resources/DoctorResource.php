<?php

declare(strict_types=1);

namespace Lightit\Doctors\App\Resources;

use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Lightit\Doctors\Domain\Models\Doctor;

/**
 * @mixin Doctor
 */
#[SchemaName('Doctor')]
class DoctorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
