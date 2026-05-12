<?php

declare(strict_types=1);

namespace Lightit\Patients\Domain\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Lightit\Appointments\Domain\Models\Appointment;
use Lightitlabs\Models\JWTAuthenticatable;

/**
 * @property int                  $id
 * @property string               $name
 * @property string               $email
 * @property CarbonImmutable      $created_at
 * @property CarbonImmutable      $updated_at
 * @property string|null          $password
 * @property CarbonImmutable|null $email_verified_at
 * @property string|null          $remember_token
 * @property-read Collection<int, Appointment> $appointments
 * @property-read int|null $appointments_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Patient whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Patient extends JWTAuthenticatable
{
    use Notifiable;

    #[\Override]
    protected $guarded = ['id'];

    /** @var list<string> */
    #[\Override]
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'immutable_datetime',
            'password' => 'hashed',
        ];
    }

    /** @return Attribute<string, string> */
    protected function email(): Attribute
    {
        $parser = static fn (mixed $value): string => strtolower(is_string($value) ? $value : '');

        return Attribute::make(
            get: $parser,
            set: $parser,
        );
    }

    /** @return HasMany<Appointment, $this> */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
