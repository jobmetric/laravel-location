<?php

namespace JobMetric\Location\Http\Requests\Country;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\Country as CountryModel;
use JobMetric\Location\Rules\CheckExistNameRule;

/**
 * Class StoreCountryRequest
 *
 * Validation request for storing a new Country.
 *
 * @package JobMetric\Location
 */
class StoreCountryRequest extends FormRequest
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
            'name'              => [
                'required',
                'string',
                'max:255',
                new CheckExistNameRule(CountryModel::class),
            ],
            'flag'              => 'nullable|string|max:255',
            'mobile_prefix'     => 'nullable|integer|min:1|max:999',
            'validation'        => 'nullable|array',
            'address_on_letter' => 'nullable|string',
            'status'            => 'sometimes|boolean',
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
            'name'              => trans('location::base.fields.name'),
            'flag'              => trans('location::base.fields.flag'),
            'mobile_prefix'     => trans('location::base.fields.mobile_prefix'),
            'validation'        => trans('location::base.fields.validation'),
            'address_on_letter' => trans('location::base.fields.address_on_letter'),
            'status'            => trans('location::base.fields.status'),
        ];
    }
}
