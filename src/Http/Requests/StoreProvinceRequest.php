<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\Province as ProvinceModel;
use JobMetric\Location\Rules\CheckExistNameRule;

/**
 * Class StoreProvinceRequest
 *
 * Validation request for storing a new Province.
 *
 * @package JobMetric\Location
 */
class StoreProvinceRequest extends FormRequest
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
        $countryId = $this->input('country_id');

        return [
            'country_id' => [
                'required',
                'integer',
                'exists:' . config('location.tables.country') . ',id',
            ],
            'name'       => [
                'required',
                'string',
                'max:255',
                new CheckExistNameRule(ProvinceModel::class, null, $countryId),
            ],
            'status'     => 'sometimes|boolean',
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
            'country_id' => trans('location::base.model_name.country'),
            'name'       => trans('location::base.model_name.province'),
            'status'     => trans('location::base.model_name.province'),
        ];
    }
}
