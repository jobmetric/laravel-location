<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\District as DistrictModel;
use JobMetric\Location\Rules\CheckExistNameRule;

/**
 * Class StoreDistrictRequest
 *
 * Validation request for storing a new District.
 *
 * @package JobMetric\Location
 */
class StoreDistrictRequest extends FormRequest
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
        $cityId = $this->input('city_id');

        return [
            'city_id' => [
                'required',
                'integer',
                'exists:' . config('location.tables.city') . ',id',
            ],
            'name'    => [
                'required',
                'string',
                'max:255',
                new CheckExistNameRule(DistrictModel::class, null, $cityId),
            ],
            'status'  => 'sometimes|boolean',
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
            'city_id' => trans('location::base.model_name.city'),
            'name'    => trans('location::base.model_name.district'),
            'status'  => trans('location::base.model_name.district'),
        ];
    }
}
