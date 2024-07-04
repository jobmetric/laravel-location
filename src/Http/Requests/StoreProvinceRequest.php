<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationProvince;
use JobMetric\Location\Rules\CheckExistNameRule;

class StoreProvinceRequest extends FormRequest
{
    public int|null $location_country_id = null;

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
                new CheckExistNameRule(LocationProvince::class, parent_id: $location_country_id)
            ],
            'status' => 'boolean',
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
}
