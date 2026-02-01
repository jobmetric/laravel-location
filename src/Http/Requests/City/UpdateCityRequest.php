<?php

namespace JobMetric\Location\Http\Requests\City;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\City as CityModel;
use JobMetric\Location\Rules\CheckExistNameRule;

/**
 * Class UpdateCityRequest
 *
 * Validation request for updating an existing City.
 *
 * @package JobMetric\Location
 */
class UpdateCityRequest extends FormRequest
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
        $cityId = (int) ($context['city_id'] ?? $input['city_id'] ?? null);
        $provinceId = (int) ($context['province_id'] ?? $input['province_id'] ?? null);

        return [
            'province_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:' . config('location.tables.province') . ',id',
            ],
            'name'        => [
                'sometimes',
                'required',
                'string',
                'max:255',
                new CheckExistNameRule(CityModel::class, $cityId, $provinceId),
            ],
            'status'      => 'sometimes|boolean',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $cityId = (int) ($this->context['city_id'] ?? $this->input('city_id') ?? null);
        $provinceId = (int) ($this->context['province_id'] ?? $this->input('province_id') ?? null);

        return self::rulesFor($this->all(), [
            'city_id'     => $cityId,
            'province_id' => $provinceId,
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
            'province_id' => trans('location::base.fields.province_id'),
            'name'        => trans('location::base.fields.name'),
            'status'      => trans('location::base.fields.status'),
        ];
    }
}
