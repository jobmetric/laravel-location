<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationProvince;
use JobMetric\Location\Rules\CheckExistNameRule;

class UpdateProvinceRequest extends FormRequest
{
    public int|null $location_country_id = null;
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
            $location_province_id = $this->route()->parameter('location_province')?->id;
        } else {
            $location_province_id = $this->location_province_id;
        }

        if (is_null($this->location_country_id)) {
            $location_country_id = $this->route()->parameter('location_province')?->location_country_id;
            if (is_null($location_country_id)) {
                $location_country_id = $this->input('location_country_id');
            }
        } else {
            $location_country_id = $this->location_country_id;
        }

        return [
            'location_country_id' => 'required|exists:' . config('location.tables.country') . ',id',
            'name' => [
                'string',
                'sometimes',
                new CheckExistNameRule(LocationProvince::class, $location_province_id, $location_country_id)
            ],
            'status' => 'boolean|sometimes',
        ];
    }

    /**
     * Set country id for validation
     *
     * @param int $location_country_id
     * @return static
     */
    public function setLocationCountryId(int $location_country_id): static
    {
        $this->location_country_id = $location_country_id;

        return $this;
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
}
