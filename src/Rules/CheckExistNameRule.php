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
        private int|null $object_id = null,
        private int|null $parent_id = null,
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
        $query = $this->model::where('name', $value);

        if ($this->object_id) {
            $query->where('id', '!=', $this->object_id);
        }

        if ($this->parent_id) {
            if ($this->model === 'JobMetric\Location\Models\LocationProvince') {
                $query->where(config('location.foreign_key.country'), $this->parent_id);
            }

            if($this->model === 'JobMetric\Location\Models\LocationCity') {
                $query->where(config('location.foreign_key.province'), $this->parent_id);
            }

            if($this->model === 'JobMetric\Location\Models\LocationDistrict') {
                $query->where(config('location.foreign_key.city'), $this->parent_id);
            }
        }

        if ($query->exists()) {
            $fail(__('location::base.validation.check_exist_name'));
        }
    }
}
