<?php

namespace JobMetric\Location\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\LocationCity;
use JobMetric\Location\Rules\CheckExistNameRule;

class StoreCityRequest extends FormRequest
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
            config('location.foreign_key.province') => 'required|exists:' . config('location.tables.province') . ',id',
            'name' => [
                'string',
                new CheckExistNameRule(LocationCity::class)
            ],
            'status' => 'boolean',
        ];
    }
}
