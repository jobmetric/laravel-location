<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreAddressRequest
 *
 * Validation request for storing a new Address.
 *
 * @package JobMetric\Location
 */
class StoreAddressRequest extends FormRequest
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
            'country_id'         => [
                'required',
                'integer',
                'exists:' . config('location.tables.country') . ',id',
            ],
            'province_id'        => [
                'required',
                'integer',
                'exists:' . config('location.tables.province') . ',id',
            ],
            'city_id'            => [
                'required',
                'integer',
                'exists:' . config('location.tables.city') . ',id',
            ],
            'district_id'        => [
                'nullable',
                'integer',
                'exists:' . config('location.tables.district') . ',id',
            ],
            'address'            => [
                'required',
                'array',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $allowed = ['blvd', 'street', 'alley', 'number', 'floor', 'unit'];
                    $keys = is_array($value) ? array_keys($value) : [];
                    $invalid = array_diff($keys, $allowed);
                    if ($invalid !== []) {
                        $fail(trans('location::base.validation.address_keys_only', [
                            'allowed' => implode(', ', $allowed),
                            'invalid' => implode(', ', $invalid),
                        ]));
                    }
                },
            ],
            'address.blvd'       => 'nullable|string|max:255',
            'address.street'     => 'nullable|string|max:255',
            'address.alley'      => 'nullable|string|max:255',
            'address.number'     => 'nullable|string|max:50',
            'address.floor'      => 'nullable|string|max:50',
            'address.unit'       => 'nullable|string|max:50',
            'postcode'           => 'nullable|string|max:20',
            'lat'                => 'nullable|string|max:20',
            'lng'                => 'nullable|string|max:20',
            'info'               => [
                'nullable',
                'array',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_array($value) || $value === []) {
                        return;
                    }
                    $allowed = ['mobile_prefix', 'mobile', 'name', 'landline', 'notes'];
                    $keys = array_keys($value);
                    $invalid = array_diff($keys, $allowed);
                    if ($invalid !== []) {
                        $fail(trans('location::base.validation.info_keys_only', [
                            'allowed' => implode(', ', $allowed),
                            'invalid' => implode(', ', $invalid),
                        ]));
                    }
                },
            ],
            'info.mobile_prefix' => 'nullable|string|max:20',
            'info.mobile'        => 'nullable|string|max:50',
            'info.name'          => 'nullable|string|max:255',
            'info.landline'      => 'nullable|string|max:50',
            'info.notes'         => 'nullable|string',
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
            'address'     => trans('location::base.model_name.address'),
            'postcode'    => trans('location::base.model_name.address'),
            'lat'         => trans('location::base.model_name.address'),
            'lng'         => trans('location::base.model_name.address'),
            'info'        => trans('location::base.model_name.address'),
        ];
    }
}
