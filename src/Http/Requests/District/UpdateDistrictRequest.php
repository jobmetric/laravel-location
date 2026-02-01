<?php

namespace JobMetric\Location\Http\Requests\District;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\District as DistrictModel;
use JobMetric\Location\Rules\CheckExistNameRule;

/**
 * Class UpdateDistrictRequest
 *
 * Validation request for updating an existing District.
 *
 * @package JobMetric\Location
 */
class UpdateDistrictRequest extends FormRequest
{
    /**
     * External context (injected via dto()).
     *
     * @var array<string,mixed>
     */
    protected array $context = [];

    /**
     * Set context for validation.
     *
     * @param array<string,mixed> $context
     *
     * @return void
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Build validation rules dynamically.
     *
     * @param array<string,mixed> $input
     * @param array<string,mixed> $context
     *
     * @return array<string,mixed>
     */
    public static function rulesFor(array $input, array $context = []): array
    {
        $districtId = (int) ($context['district_id'] ?? $input['district_id'] ?? null);
        $cityId = (int) ($context['city_id'] ?? $input['city_id'] ?? null);

        return [
            'city_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:' . config('location.tables.city') . ',id',
            ],
            'name'    => [
                'sometimes',
                'required',
                'string',
                'max:255',
                new CheckExistNameRule(DistrictModel::class, $districtId, $cityId),
            ],
            'status'  => 'sometimes|boolean',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $districtId = (int) ($this->context['district_id'] ?? $this->input('district_id') ?? null);
        $cityId = (int) ($this->context['city_id'] ?? $this->input('city_id') ?? null);

        return self::rulesFor($this->all(), [
            'district_id' => $districtId,
            'city_id'     => $cityId,
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'city_id' => trans('location::base.fields.city_id'),
            'name'    => trans('location::base.fields.name'),
            'status'  => trans('location::base.fields.status'),
        ];
    }
}
