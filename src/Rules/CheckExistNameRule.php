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
        // Check if the object_id and parent_id are set
        if($this->object_id && $this->parent_id) {
            $query = $this->model::where('name', $value)->where('id', '!=', $this->object_id);

            if ($this->model === 'JobMetric\Location\Models\LocationProvince') {
                $query->where(config('location.foreign_key.country'), $this->parent_id);
            }

            if($this->model === 'JobMetric\Location\Models\LocationCity') {
                $query->where(config('location.foreign_key.province'), $this->parent_id);
            }

            if($this->model === 'JobMetric\Location\Models\LocationDistrict') {
                $query->where(config('location.foreign_key.city'), $this->parent_id);
            }

            if ($query->exists()) {
                $fail(__('location::base.validation.check_exist_name'));
            }

            return;
        }

        // Check if the object_id is set
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
