<?php

namespace JobMetric\Location\Http\Requests\Province;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\Province as ProvinceModel;
use JobMetric\Location\Rules\CheckExistNameRule;

/**
 * Class UpdateProvinceRequest
 *
 * Validation request for updating an existing Province.
 *
 * @package JobMetric\Location
 */
class UpdateProvinceRequest extends FormRequest
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
        $provinceId = (int) ($context['province_id'] ?? $input['province_id'] ?? null);
        $countryId = (int) ($context['country_id'] ?? $input['country_id'] ?? null);

        return [
            'country_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:' . config('location.tables.country') . ',id',
            ],
            'name'       => [
                'sometimes',
                'required',
                'string',
                'max:255',
                new CheckExistNameRule(ProvinceModel::class, $provinceId, $countryId),
            ],
            'status'     => 'sometimes|boolean',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $provinceId = (int) ($this->context['province_id'] ?? $this->input('province_id') ?? null);
        $countryId = (int) ($this->context['country_id'] ?? $this->input('country_id') ?? null);

        return self::rulesFor($this->all(), [
            'province_id' => $provinceId,
            'country_id'  => $countryId,
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
            'country_id' => trans('location::base.fields.country_id'),
            'name'       => trans('location::base.fields.name'),
            'status'     => trans('location::base.fields.status'),
        ];
    }
}
