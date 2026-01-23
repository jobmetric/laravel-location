<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\City as CityModel;
use JobMetric\Location\Rules\CheckExistNameRule;

/**
 * Class StoreCityRequest
 *
 * Validation request for storing a new City.
 *
 * @package JobMetric\Location
 */
class StoreCityRequest extends FormRequest
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
        $provinceId = $this->input('province_id');

        return [
            'province_id' => [
                'required',
                'integer',
                'exists:' . config('location.tables.province') . ',id',
            ],
            'name'        => [
                'required',
                'string',
                'max:255',
                new CheckExistNameRule(CityModel::class, null, $provinceId),
            ],
            'status'      => 'sometimes|boolean',
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
            'province_id' => trans('location::base.model_name.province'),
            'name'        => trans('location::base.model_name.city'),
            'status'      => trans('location::base.model_name.city'),
        ];
    }
}
