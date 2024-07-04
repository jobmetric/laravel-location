<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationDistrict;
use JobMetric\Location\Rules\CheckExistNameRule;

class UpdateDistrictRequest extends FormRequest
{
    public int|null $location_city_id = null;
    public int|null $location_district_id = null;

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
        if (is_null($this->location_district_id)) {
            $location_district_id = $this->route()->parameter('location_district')?->id;
        } else {
            $location_district_id = $this->location_district_id;
        }

        if (is_null($this->location_city_id)) {
            $location_city_id = $this->route()->parameter('location_district')?->location_city_id;
            if (is_null($location_city_id)) {
                $location_city_id = $this->input('location_city_id');
            }
        } else {
            $location_city_id = $this->location_city_id;
        }

        return [
            'location_country_id' => 'required|exists:' . config('location.tables.country') . ',id',
            'location_province_id' => 'required|exists:' . config('location.tables.province') . ',id',
            'location_city_id' => 'required|exists:' . config('location.tables.city') . ',id',
            'name' => [
                'string',
                'sometimes',
                new CheckExistNameRule(LocationDistrict::class, $location_district_id, $location_city_id)
            ],
            'status' => 'boolean|sometimes',
        ];
    }

    /**
     * Set city id for validation
     *
     * @param int|null $location_city_id
     * @return static
     */
    public function setLocationCityId(int $location_city_id = null): static
    {
        $this->location_city_id = $location_city_id;

        return $this;
    }

    /**
     * Set district id for validation
     *
     * @param int $location_district_id
     * @return static
     */
    public function setLocationDistrictId(int $location_district_id): static
    {
        $this->location_district_id = $location_district_id;

        return $this;
    }
}
