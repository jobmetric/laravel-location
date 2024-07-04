<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGeoAreaRequest extends FormRequest
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
            'title' => 'string|unique:' . config('location.tables.geo_area') . ',title',
            'description' => 'string|nullable',
            'status' => 'boolean',

            'geo_area_zones' => 'array|nullable|sometimes',
            'geo_area_zones.*' => 'array',
            'geo_area_zones.*.location_country_id' => 'integer|exists:' . config('location.tables.country') . ',id',
            'geo_area_zones.*.location_province_id' => 'integer|exists:' . config('location.tables.province') . ',id|nullable',
            'geo_area_zones.*.location_city_id' => 'integer|exists:' . config('location.tables.city') . ',id|nullable',
            'geo_area_zones.*.location_district_id' => 'integer|exists:' . config('location.tables.district') . ',id|nullable',
        ];
    }
}
