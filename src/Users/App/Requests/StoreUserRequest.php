<?php

declare(strict_types=1);

namespace Lightit\Users\App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;
use Illuminate\Validation\Rules\Password;
use Lightit\Users\Domain\DataTransferObjects\StoreUserDto;
use Lightit\Users\Domain\Models\User;

class StoreUserRequest extends FormRequest
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
            self::NAME => ['required', 'string', 'min:4', 'max:80'],
            self::EMAIL => [
                'required',
                'max:100',
                Email::default(),
                Rule::unique(User::class, 'email'),
            ],
            self::PASSWORD => [
                'required',
                Password::default(),
                'confirmed',
            ],
        ];
    }

    public function toDto(): StoreUserDto
    {
        return new StoreUserDto(
            name: $this->string(self::NAME)->toString(),
            emailAddress: $this->string(self::EMAIL)->toString(),
            password: $this->string(self::PASSWORD)->toString(),
        );
    }
}
