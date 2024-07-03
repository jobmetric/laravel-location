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
            'title' => 'string|unique:'.config('location.tables.geo_area').',title',
            'description' => 'string|nullable',
            'status' => 'boolean',

            'geo_area_zones' => 'array|nullable|sometimes',
            'geo_area_zones.*' => 'array',
            'geo_area_zones.*.'.config('location.foreign_key.country') => 'integer|exists:'.config('location.tables.country').',id',
            'geo_area_zones.*.'.config('location.foreign_key.province') => 'integer|exists:'.config('location.tables.province').',id|nullable',
            'geo_area_zones.*.'.config('location.foreign_key.city') => 'integer|exists:'.config('location.tables.city').',id|nullable',
            'geo_area_zones.*.'.config('location.foreign_key.district') => 'integer|exists:'.config('location.tables.district').',id|nullable',
        ];
    }
}
