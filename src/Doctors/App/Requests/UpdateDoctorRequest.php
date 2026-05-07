<?php

declare(strict_types=1);

namespace Lightit\Doctors\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Lightit\Doctors\Domain\DataTransferObjects\UpdateDoctorDto;

class UpdateDoctorRequest extends FormRequest
{
    public const string NAME = 'name';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            self::NAME => ['sometimes', 'string', 'min:4', 'max:80'],
        ];
    }

    public function toDto(): UpdateDoctorDto
    {
        return new UpdateDoctorDto(
            name: $this->has(self::NAME) ? $this->string(self::NAME)->toString() : null,
        );
    }
}
