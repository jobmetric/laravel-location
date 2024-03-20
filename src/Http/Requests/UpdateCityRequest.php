<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationCity;
use JobMetric\Location\Rules\CheckExistNameRule;

class UpdateCityRequest extends FormRequest
{
    public int|null $location_province_id = null;
    public int|null $location_city_id = null;

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
        if (is_null($this->location_city_id)) {
            $location_city_id = $this->route()->parameter('location_city')->id;
        } else {
            $location_city_id = $this->location_city_id;
        }

        if (is_null($this->location_province_id)) {
            $location_province_id = $this->input('location_province_id');
        } else {
            $location_province_id = $this->location_province_id;
        }

        return [
            config('location.foreign_key.province') => 'required|exists:' . config('location.tables.province') . ',id',
            'name' => [
                'string',
                'sometimes',
                new CheckExistNameRule(LocationCity::class, $location_city_id, $location_province_id)
            ],
            'status' => 'boolean|sometimes',
        ];
    }

    /**
     * Set province id for validation
     *
     * @param int $location_province_id
     * @return static
     */
    public function setLocationProvinceId(int $location_province_id): static
    {
        $this->location_province_id = $location_province_id;

        return $this;
    }

    /**
     * Set city id for validation
     *
     * @param int $location_city_id
     * @return static
     */
    public function setLocationCityId(int $location_city_id): static
    {
        $this->location_city_id = $location_city_id;

        return $this;
    }
}
