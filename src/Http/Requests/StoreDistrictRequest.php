<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationDistrict;
use JobMetric\Location\Rules\CheckExistNameRule;

class StoreDistrictRequest extends FormRequest
{
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
            $location_city_id = $this->route()->parameter('location_city')?->{config('location.foreign_key.city')};
            if (is_null($location_city_id)) {
                $location_city_id = $this->input('location_city_id');
            }
        } else {
            $location_city_id = $this->location_city_id;
        }

        return [
            config('location.foreign_key.country') => 'required|exists:' . config('location.tables.country') . ',id',
            config('location.foreign_key.province') => 'required|exists:' . config('location.tables.province') . ',id',
            config('location.foreign_key.city') => 'required|exists:' . config('location.tables.city') . ',id',
            'name' => [
                'string',
                new CheckExistNameRule(LocationDistrict::class, parent_id: $location_city_id)
            ],
            'status' => 'boolean',
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
}
