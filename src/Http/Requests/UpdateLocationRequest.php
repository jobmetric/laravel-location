<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateLocationRequest
 *
 * Validation request for updating an existing Location.
 *
 * @package JobMetric\Location
 */
class UpdateLocationRequest extends FormRequest
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
        return [
            'country_id'  => [
                'sometimes',
                'required',
                'integer',
                'exists:' . config('location.tables.country') . ',id',
            ],
            'province_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:' . config('location.tables.province') . ',id',
            ],
            'city_id'     => [
                'sometimes',
                'nullable',
                'integer',
                'exists:' . config('location.tables.city') . ',id',
            ],
            'district_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:' . config('location.tables.district') . ',id',
            ],
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return self::rulesFor($this->all(), $this->context);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'country_id'  => trans('location::base.model_name.country'),
            'province_id' => trans('location::base.model_name.province'),
            'city_id'     => trans('location::base.model_name.city'),
            'district_id' => trans('location::base.model_name.district'),
        ];
    }
}
