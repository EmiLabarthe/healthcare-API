<?php

declare(strict_types=1);

namespace Lightit\Doctors\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Lightit\Doctors\Domain\DataTransferObjects\StoreDoctorDto;

class StoreDoctorRequest extends FormRequest
{
    public const string NAME = 'name';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            self::NAME => ['required', 'string', 'min:4', 'max:80'],
        ];
    }

    public function toDto(): StoreDoctorDto
    {
        return new StoreDoctorDto(
            name: $this->string(self::NAME)->toString(),
        );
    }
}
