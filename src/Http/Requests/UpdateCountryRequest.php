<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationCountry;
use JobMetric\Location\Rules\CheckExistNameRule;

class UpdateCountryRequest extends FormRequest
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
            $location_country_id = $this->route()->parameter('location_country')->id;
        } else {
            $location_country_id = $this->location_country_id;
        }

        return [
            'name' => [
                'string',
                'sometimes',
                new CheckExistNameRule(LocationCountry::class, $location_country_id)
            ],
            'flag' => 'string|nullable|sometimes',
            'mobile_prefix' => 'integer|nullable|sometimes',
            'validation' => 'array|nullable|sometimes',
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
}
