<?php

namespace JobMetric\Location\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CheckExistNameRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $model,
        private int|null $object_id = null
    )
    {
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->object_id) {
            if ($this->model::where('name', $value)->where('id', '!=', $this->object_id)->exists()) {
                $fail(__('location::base.validation.check_exist_name'));
            }

            return;
        }

        if ($this->model::where('name', $value)->exists()) {
            $fail(__('location::base.validation.check_exist_name'));
        }
    }
}
