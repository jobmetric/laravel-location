<?php

namespace JobMetric\Location\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use JobMetric\Location\Models\City;
use JobMetric\Location\Models\District;
use JobMetric\Location\Models\Province;

class CheckExistNameRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $model,
        private int|null $object_id = null,
        private int|null $parent_id = null,
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = $this->model::where('name', $value);

        if ($this->object_id) {
            $query->where('id', '!=', $this->object_id);
        }

        if ($this->parent_id) {
            if ($this->model === Province::class) {
                $query->where('country_id', $this->parent_id);
            }

            if ($this->model === City::class) {
                $query->where('province_id', $this->parent_id);
            }

            if ($this->model === District::class) {
                $query->where('city_id', $this->parent_id);
            }
        }

        if ($query->exists()) {
            $fail(__('location::base.validation.check_exist_name'));
        }
    }
}
