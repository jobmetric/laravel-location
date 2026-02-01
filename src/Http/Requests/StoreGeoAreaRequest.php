<?php

namespace JobMetric\Location\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreGeoAreaRequest
 *
 * Validation request for storing a new GeoArea.
 *
 * @package JobMetric\Location
 */
class StoreGeoAreaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'translation'               => 'required|array',
            'translation.*'             => 'array',
            'translation.*.name'        => 'required|string|max:255',
            'translation.*.description' => 'nullable|string',

            'status' => 'sometimes|boolean',

            'locations'               => [
                'nullable',
                'array',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! is_array($value)) {
                        return;
                    }

                    // Check for duplicate locations
                    $locationKeys = [];
                    foreach ($value as $index => $location) {
                        $key = implode('-', [
                            $location['country_id'] ?? 0,
                            $location['province_id'] ?? 0,
                            $location['city_id'] ?? 0,
                            $location['district_id'] ?? 0,
                        ]);

                        if (in_array($key, $locationKeys, true)) {
                            $fail(trans('location::base.validation.duplicate_location'));

                            return;
                        }

                        $locationKeys[] = $key;
                    }
                },
            ],
            'locations.*'             => 'array',
            'locations.*.country_id'  => 'required|integer|exists:' . config('location.tables.country') . ',id',
            'locations.*.province_id' => 'nullable|integer|exists:' . config('location.tables.province') . ',id',
            'locations.*.city_id'     => 'nullable|integer|exists:' . config('location.tables.city') . ',id',
            'locations.*.district_id' => 'nullable|integer|exists:' . config('location.tables.district') . ',id',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'translation'               => trans('location::base.fields.translation'),
            'translation.*.name'        => trans('location::base.fields.name'),
            'translation.*.description' => trans('location::base.fields.description'),
            'status'                    => trans('location::base.fields.status'),
            'locations'                 => trans('location::base.fields.locations'),
            'locations.*.country_id'    => trans('location::base.fields.country_id'),
            'locations.*.province_id'   => trans('location::base.fields.province_id'),
            'locations.*.city_id'       => trans('location::base.fields.city_id'),
            'locations.*.district_id'   => trans('location::base.fields.district_id'),
        ];
    }
}
