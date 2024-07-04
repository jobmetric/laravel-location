<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'country_id' => 'required|exists:' . config('location.tables.country') . ',id',
            'province_id' => 'required|exists:' . config('location.tables.province') . ',id',
            'city_id' => 'required|exists:' . config('location.tables.city') . ',id',
            'district_id' => 'nullable|exists:' . config('location.tables.district') . ',id',
            'address' => 'required|string|max:255',
            'pluck' => 'nullable|string|max:10',
            'unit' => 'nullable|string|max:20',
            'postcode' => 'nullable|string|max:20',
            'lat' => 'nullable|string|max:20',
            'lng' => 'nullable|string|max:20',

            'info' => 'array|nullable',
            'info.*.mobile_prefix' => 'required|string|max:20',
            'info.*.mobile' => 'required|string|max:50',
            'info.*.name' => 'required|string|max:255',
        ];
    }
}
