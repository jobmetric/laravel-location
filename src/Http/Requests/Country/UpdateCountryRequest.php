<?php

namespace JobMetric\Location\Http\Requests\Country;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use JobMetric\Location\Models\Country as CountryModel;
use JobMetric\Location\Rules\CheckExistNameRule;

/**
 * Class UpdateCountryRequest
 *
 * Validation request for updating an existing Country.
 *
 * @package JobMetric\Location
 */
class UpdateCountryRequest extends FormRequest
{
    /**
     * External context (injected via dto()).
     *
     * @var array<string,mixed>
     */
    protected array $context = [];

    /**
     * Set context for validation.
     *
     * @param array<string,mixed> $context
     *
     * @return void
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

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
     * Build validation rules dynamically.
     *
     * @param array<string,mixed> $input
     * @param array<string,mixed> $context
     *
     * @return array<string,mixed>
     */
    public static function rulesFor(array $input, array $context = []): array
    {
        $countryId = (int) ($context['country_id'] ?? $input['country_id'] ?? null);

        return [
            'name'              => [
                'sometimes',
                'required',
                'string',
                'max:255',
                new CheckExistNameRule(CountryModel::class, $countryId),
            ],
            'flag'              => 'sometimes|nullable|string|max:255',
            'mobile_prefix'     => 'sometimes|nullable|integer|min:1|max:999',
            'validation'        => 'sometimes|nullable|array',
            'address_on_letter' => 'sometimes|nullable|string',
            'status'            => 'sometimes|boolean',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $countryId = (int) ($this->context['country_id'] ?? $this->input('country_id') ?? null);

        return self::rulesFor($this->all(), [
            'country_id' => $countryId,
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'              => trans('location::base.fields.name'),
            'flag'              => trans('location::base.fields.flag'),
            'mobile_prefix'     => trans('location::base.fields.mobile_prefix'),
            'validation'        => trans('location::base.fields.validation'),
            'address_on_letter' => trans('location::base.fields.address_on_letter'),
            'status'            => trans('location::base.fields.status'),
        ];
    }
}
