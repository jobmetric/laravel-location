<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGeoAreaRequest extends FormRequest
{
    public int|null $location_geo_area_id = null;

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
        if (is_null($this->location_geo_area_id)) {
            $location_geo_area_id = $this->route()->parameter('location_geo_area')?->id;
        } else {
            $location_geo_area_id = $this->location_geo_area_id;
        }

        return [
            'title' => 'string|unique:' . config('location.tables.geo_area') . ',title,' . $location_geo_area_id . '|sometimes',
            'description' => 'string|nullable|sometimes',
            'status' => 'boolean|sometimes',

            'geo_area_zones' => 'array|nullable|sometimes',
            'geo_area_zones.*' => 'array',
            'geo_area_zones.*.' . config('location.foreign_key.country') => 'integer|exists:' . config('location.tables.country') . ',id',
            'geo_area_zones.*.' . config('location.foreign_key.province') => 'integer|exists:' . config('location.tables.province') . ',id|nullable',
            'geo_area_zones.*.' . config('location.foreign_key.city') => 'integer|exists:' . config('location.tables.city') . ',id|nullable',
            'geo_area_zones.*.' . config('location.foreign_key.district') => 'integer|exists:' . config('location.tables.district') . ',id|nullable',
        ];
    }

    /**
     * Set province id for validation
     *
     * @param int|null $location_province_id
     * @return static
     */
    public function setLocationProvinceId(int $location_province_id = null): static
    {
        $this->location_province_id = $location_province_id;

        return $this;
    }

    /**
     * Set city id for validation
     *
     * @param int $location_geo_area_id
     * @return static
     */
    public function setLocationGeoAreaId(int $location_geo_area_id): static
    {
        $this->location_geo_area_id = $location_geo_area_id;

        return $this;
    }
}
