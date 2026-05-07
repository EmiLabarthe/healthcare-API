<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;
use Lightit\Patients\Domain\DataTransferObjects\UpdatePatientDto;
use Lightit\Patients\Domain\Models\Patient;

class UpdatePatientRequest extends FormRequest
{
    public const string NAME = 'name';

    public const string EMAIL = 'email';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            self::NAME => ['sometimes', 'string', 'min:4', 'max:80'],
            self::EMAIL => [
                'sometimes',
                'max:100',
                Email::default(),
                Rule::unique(Patient::class, 'email')->ignore($this->route('patient')?->id),
            ],
        ];
    }

    public function toDto(): UpdatePatientDto
    {
        return new UpdatePatientDto(
            name: $this->has(self::NAME) ? $this->string(self::NAME)->toString() : null,
            email: $this->has(self::EMAIL) ? $this->string(self::EMAIL)->toString() : null,
        );
    }
}
