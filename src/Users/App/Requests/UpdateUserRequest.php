<?php

declare(strict_types=1);

namespace Lightit\Users\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;
use Illuminate\Validation\Rules\Password;
use Lightit\Users\Domain\DataTransferObjects\UpdateUserDto;
use Lightit\Users\Domain\Models\User;

class UpdateUserRequest extends FormRequest
{
    public const string NAME = 'name';

    public const string EMAIL = 'email_address';

    public const string PASSWORD = 'password';

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
                Rule::unique(User::class, 'email')->ignore($this->route('user')->id),
            ],
            self::PASSWORD => [
                'sometimes',
                Password::default(),
                'confirmed',
            ],
        ];
    }

    public function toDto(): UpdateUserDto
    {
        return new UpdateUserDto(
            name: $this->has(self::NAME) ? $this->string(self::NAME)->toString() : null,
            emailAddress: $this->has(self::EMAIL) ? $this->string(self::EMAIL)->toString() : null,
            password: $this->has(self::PASSWORD) ? $this->string(self::PASSWORD)->toString() : null,
        );
    }
}
