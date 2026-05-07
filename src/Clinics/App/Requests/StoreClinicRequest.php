<?php

declare(strict_types=1);

namespace Lightit\Clinics\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Lightit\Clinics\Domain\DataTransferObjects\StoreClinicDto;

class StoreClinicRequest extends FormRequest
{
    public const string NAME = 'name';

    public const string ADDRESS = 'address';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            self::NAME => ['required', 'string', 'min:4', 'max:80'],
            self::ADDRESS => ['required', 'string', 'max:255'],
        ];
    }

    public function toDto(): StoreClinicDto
    {
        return new StoreClinicDto(
            name: $this->string(self::NAME)->toString(),
            address: $this->string(self::ADDRESS)->toString(),
        );
    }
}
