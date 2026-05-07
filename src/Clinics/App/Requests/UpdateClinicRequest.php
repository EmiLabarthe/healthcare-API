<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Lightit\Clinics\Domain\DataTransferObjects\UpdateClinicDto;

class UpdateClinicRequest extends FormRequest
{
    public const string NAME = 'name';

    public const string ADDRESS = 'address';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            self::NAME => ['sometimes', 'string', 'min:4', 'max:80'],
            self::ADDRESS => ['sometimes', 'string', 'max:255'],
        ];
    }

    public function toDto(): UpdateClinicDto
    {
        return new UpdateClinicDto(
            name: $this->has(self::NAME) ? $this->string(self::NAME)->toString() : null,
            address: $this->has(self::ADDRESS) ? $this->string(self::ADDRESS)->toString() : null,
        );
    }
}
