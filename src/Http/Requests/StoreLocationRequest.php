<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreLocationRequest
 *
 * Validation request for storing a new Location.
 *
 * @package JobMetric\Location
 */
class StoreLocationRequest extends FormRequest
{
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'country_id'  => [
                'required',
                'integer',
                'exists:' . config('location.tables.country') . ',id',
            ],
            'province_id' => [
                'nullable',
                'integer',
                'exists:' . config('location.tables.province') . ',id',
            ],
            'city_id'     => [
                'nullable',
                'integer',
                'exists:' . config('location.tables.city') . ',id',
            ],
            'district_id' => [
                'nullable',
                'integer',
                'exists:' . config('location.tables.district') . ',id',
            ],
        ];
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
