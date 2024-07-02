<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationCity;
use JobMetric\Location\Rules\CheckExistNameRule;

class StoreCityRequest extends FormRequest
{
    public int|null $location_province_id = null;

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
        if (is_null($this->location_province_id)) {
            $location_province_id = $this->route()->parameter('location_city')?->{config('location.foreign_key.province')};
            if (is_null($location_province_id)) {
                $location_province_id = $this->input('location_province_id');
            }
        } else {
            $location_province_id = $this->location_province_id;
        }

        return [
            config('location.foreign_key.country') => 'required|exists:' . config('location.tables.country') . ',id',
            config('location.foreign_key.province') => 'required|exists:' . config('location.tables.province') . ',id',
            'name' => [
                'string',
                new CheckExistNameRule(LocationCity::class, parent_id: $location_province_id)
            ],
            'status' => 'boolean',
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
}
