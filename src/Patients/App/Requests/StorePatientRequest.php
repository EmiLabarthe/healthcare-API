<?php

declare(strict_types=1);

namespace Lightit\Patients\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;
use Illuminate\Validation\Rules\Password;
use Lightit\Patients\Domain\DataTransferObjects\StorePatientDto;
use Lightit\Patients\Domain\Models\Patient;

class StorePatientRequest extends FormRequest
{
    public const string NAME = 'name';

    public const string EMAIL = 'email';

    public const string PASSWORD = 'password';

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            self::NAME => ['required', 'string', 'min:4', 'max:80'],
            self::EMAIL => [
                'required',
                'max:100',
                Email::default(),
                Rule::unique(Patient::class, 'email'),
            ],
            self::PASSWORD => ['required', 'string', Password::default()],
        ];
    }

    public function toDto(): StorePatientDto
    {
        return new StorePatientDto(
            name: $this->string(self::NAME)->toString(),
            email: $this->string(self::EMAIL)->toString(),
            password: $this->string(self::PASSWORD)->toString(),
        );
    }
}
