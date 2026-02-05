<?php

namespace JobMetric\Location\Http\Requests\District;

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
            'subtitle' => 'sometimes|nullable|string|max:255',
            'keywords' => 'sometimes|nullable|array',
            'keywords.*' => 'string|max:100',
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
            'city_id' => trans('location::base.fields.city_id'),
            'name'    => trans('location::base.fields.name'),
            'subtitle' => trans('location::base.fields.subtitle'),
            'keywords' => trans('location::base.fields.keywords'),
            'status'  => trans('location::base.fields.status'),
        ];
    }
}
