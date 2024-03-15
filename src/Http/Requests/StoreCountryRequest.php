<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationCountry;
use JobMetric\Location\Rules\CheckExistNameRule;

class StoreCountryRequest extends FormRequest
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
            'name' => [
                'string',
                new CheckExistNameRule(LocationCountry::class)
            ],
            'flag' => 'string|nullable',
            'mobile_prefix' => 'integer|nullable',
            'validation' => 'array|nullable',
            'status' => 'boolean',
        ];
    }
}
