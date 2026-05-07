<?php

declare(strict_types=1);

namespace Lightit\Appointments\App\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lightit\Appointments\Domain\DataTransferObjects\StoreAppointmentDto;
use Lightit\Appointments\Domain\Models\Appointment;
use Lightit\Doctors\Domain\Models\Doctor;
use Lightit\Patients\Domain\Models\Patient;

class StoreAppointmentRequest extends FormRequest
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
        return [
            self::DOCTOR_ID => ['required', 'integer', Rule::exists(Doctor::class, 'id')],
            self::PATIENT_ID => ['required', 'integer', Rule::exists(Patient::class, 'id')],
            self::CLINIC_ID => [
                'required',
                'integer',
                Rule::exists('clinic_doctor', 'clinic_id')
                    ->where('doctor_id', $this->integer(self::DOCTOR_ID)),
            ],
            self::STARTS_AT => ['required', 'date', 'after_or_equal:now'],
            self::ENDS_AT => ['required', 'date', 'after:' . self::STARTS_AT],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $startsAt = CarbonImmutable::parse($this->string(self::STARTS_AT)->toString());
            $endsAt = CarbonImmutable::parse($this->string(self::ENDS_AT)->toString());
            $doctorId = $this->integer(self::DOCTOR_ID);
            $patientId = $this->integer(self::PATIENT_ID);

            if ($this->hasOverlap($doctorId, self::DOCTOR_ID, $startsAt, $endsAt)) {
                $validator->errors()->add(
                    self::STARTS_AT,
                    __('The doctor already has an appointment in this time range.'),
                );
            }

            if ($this->hasOverlap($patientId, self::PATIENT_ID, $startsAt, $endsAt)) {
                $validator->errors()->add(
                    self::STARTS_AT,
                    __('The patient already has an appointment in this time range.'),
                );
            }
        });
    }

    public function toDto(): StoreAppointmentDto
    {
        return new StoreAppointmentDto(
            doctorId: $this->integer(self::DOCTOR_ID),
            patientId: $this->integer(self::PATIENT_ID),
            clinicId: $this->integer(self::CLINIC_ID),
            startsAt: CarbonImmutable::parse($this->string(self::STARTS_AT)->toString()),
            endsAt: CarbonImmutable::parse($this->string(self::ENDS_AT)->toString()),
        );
    }

    private function hasOverlap(
        int $relatedId,
        string $column,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
    ): bool {
        return Appointment::query()
            ->where($column, $relatedId)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }
}
