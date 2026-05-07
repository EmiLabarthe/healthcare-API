<?php

declare(strict_types=1);

namespace Lightit\Appointments\App\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lightit\Appointments\Domain\DataTransferObjects\UpdateAppointmentDto;
use Lightit\Appointments\Domain\Models\Appointment;
use Lightit\Doctors\Domain\Models\Doctor;
use Lightit\Patients\Domain\Models\Patient;

class UpdateAppointmentRequest extends FormRequest
{
    public const string DOCTOR_ID = 'doctor_id';

    public const string PATIENT_ID = 'patient_id';

    public const string CLINIC_ID = 'clinic_id';

    public const string STARTS_AT = 'starts_at';

    public const string ENDS_AT = 'ends_at';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Appointment|null $existing */
        $existing = $this->route('appointment');

        return [
            self::DOCTOR_ID => ['sometimes', 'integer', Rule::exists(Doctor::class, 'id')],
            self::PATIENT_ID => ['sometimes', 'integer', Rule::exists(Patient::class, 'id')],
            self::CLINIC_ID => [
                'sometimes',
                'integer',
                Rule::exists('clinic_doctor', 'clinic_id')
                    ->where('doctor_id', $this->has(self::DOCTOR_ID)
                        ? $this->integer(self::DOCTOR_ID)
                        : $existing?->doctor_id),
            ],
            self::STARTS_AT => ['sometimes', 'date'],
            self::ENDS_AT => ['sometimes', 'date', 'after:' . self::STARTS_AT],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Appointment|null $existing */
            $existing = $this->route('appointment');

            $startsAt = CarbonImmutable::parse(
                $this->has(self::STARTS_AT)
                    ? $this->string(self::STARTS_AT)->toString()
                    : $existing?->starts_at,
            );
            $endsAt = CarbonImmutable::parse(
                $this->has(self::ENDS_AT)
                    ? $this->string(self::ENDS_AT)->toString()
                    : $existing?->ends_at,
            );
            $doctorId = $this->has(self::DOCTOR_ID)
                ? $this->integer(self::DOCTOR_ID)
                : $existing?->doctor_id;
            $patientId = $this->has(self::PATIENT_ID)
                ? $this->integer(self::PATIENT_ID)
                : $existing?->patient_id;

            if ($doctorId !== null && $this->hasOverlap($doctorId, self::DOCTOR_ID, $startsAt, $endsAt)) {
                $validator->errors()->add(
                    self::STARTS_AT,
                    __('The doctor already has an appointment in this time range.'),
                );
            }

            if ($patientId !== null && $this->hasOverlap($patientId, self::PATIENT_ID, $startsAt, $endsAt)) {
                $validator->errors()->add(
                    self::STARTS_AT,
                    __('The patient already has an appointment in this time range.'),
                );
            }
        });
    }

    public function toDto(): UpdateAppointmentDto
    {
        return new UpdateAppointmentDto(
            doctorId: $this->has(self::DOCTOR_ID) ? $this->integer(self::DOCTOR_ID) : null,
            patientId: $this->has(self::PATIENT_ID) ? $this->integer(self::PATIENT_ID) : null,
            clinicId: $this->has(self::CLINIC_ID) ? $this->integer(self::CLINIC_ID) : null,
            startsAt: $this->has(self::STARTS_AT)
                ? CarbonImmutable::parse($this->string(self::STARTS_AT)->toString())
                : null,
            endsAt: $this->has(self::ENDS_AT)
                ? CarbonImmutable::parse($this->string(self::ENDS_AT)->toString())
                : null,
        );
    }

    private function hasOverlap(
        int $relatedId,
        string $column,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
    ): bool {
        /** @var Appointment|null $existing */
        $existing = $this->route('appointment');

        return Appointment::query()
            ->where($column, $relatedId)
            ->when(
                $existing,
                fn ($query, Appointment $current) => $query->whereKeyNot($current->id),
            )
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }
}
