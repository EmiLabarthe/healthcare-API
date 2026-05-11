<?php

declare(strict_types=1);

namespace Lightit\Appointments\App\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lightit\Appointments\Domain\Actions\CheckAppointmentOverlapAction;
use Lightit\Appointments\Domain\DataTransferObjects\UpdateAppointmentDto;
use Lightit\Appointments\Domain\Models\Appointment;
use Lightit\Doctors\Domain\Models\Doctor;

class UpdateAppointmentRequest extends FormRequest
{
    public function __construct(
        private readonly CheckAppointmentOverlapAction $checkOverlap,
    ) {
        parent::__construct();
    }

    public const string DOCTOR_ID = 'doctor_id';

    public const string STARTS_AT = 'starts_at';

    public const string ENDS_AT = 'ends_at';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            self::DOCTOR_ID => ['sometimes', 'integer', Rule::exists(Doctor::class, 'id')],
            self::STARTS_AT => ['sometimes', Rule::date()->afterOrEqual('now')],
            self::ENDS_AT => ['sometimes', Rule::date()->after(self::STARTS_AT)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Appointment $existing */
            $existing = $this->route('appointment');

            $startsAt = CarbonImmutable::parse(
                $this->has(self::STARTS_AT)
                    ? $this->string(self::STARTS_AT)->toString()
                    : $existing->starts_at,
            );
            $endsAt = CarbonImmutable::parse(
                $this->has(self::ENDS_AT)
                    ? $this->string(self::ENDS_AT)->toString()
                    : $existing->ends_at,
            );
            $doctorId = $this->has(self::DOCTOR_ID)
                ? $this->integer(self::DOCTOR_ID)
                : $existing->doctor_id;

            $excludeId = $existing->id;

            if ($this->checkOverlap->execute(Doctor::class, $doctorId, $startsAt, $endsAt, $excludeId)) {
                $validator->errors()->add(
                    self::STARTS_AT,
                    __('The doctor already has an appointment in this time range.'),
                );
            }
        });
    }

    public function toDto(): UpdateAppointmentDto
    {
        return new UpdateAppointmentDto(
            doctorId: $this->has(self::DOCTOR_ID) ? $this->integer(self::DOCTOR_ID) : null,
            startsAt: $this->has(self::STARTS_AT)
                ? CarbonImmutable::parse($this->string(self::STARTS_AT)->toString())
                : null,
            endsAt: $this->has(self::ENDS_AT)
                ? CarbonImmutable::parse($this->string(self::ENDS_AT)->toString())
                : null,
        );
    }
}
