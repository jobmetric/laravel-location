<?php

namespace JobMetric\Location\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Translation\PotentiallyTranslatedString;
use InvalidArgumentException;
use JobMetric\Location\Models\City;
use JobMetric\Location\Models\District;
use JobMetric\Location\Models\Province;

class CheckExistNameRule implements ValidationRule, DataAwareRule
{
    /**
     * Map of model => its parent foreign key column.
     *
     * @var array<class-string<Model>, string>
     */
    private const PARENT_KEY_MAP = [
        Province::class => 'country_id',
        City::class     => 'province_id',
        District::class => 'city_id',
    ];

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $model,
        private readonly int|null $object_id = null,
        private readonly int|null $parent_id = null,
    ) {
    }

    /**
     * The full data under validation (provided by the validator).
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Set the data under validation.
     *
     * @param array<string, mixed> $data
     *
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_subclass_of($this->model, Model::class)) {
            throw new InvalidArgumentException(sprintf('CheckExistNameRule expects an Eloquent model class-string, got [%s].', $this->model));
        }

        /** @var Builder $query */
        $query = $this->model::query()->where('name', (string) $value);

        if ($this->object_id !== null) {
            $query->where('id', '!=', $this->object_id);
        }

        $parentKey = self::PARENT_KEY_MAP[$this->model] ?? null;
        if ($parentKey) {
            $parentId = $this->parent_id;

            // When this rule is used inside dto()/FormRequest validation, the request object
            // may not provide input() as expected; derive the parent id from validated payload.
            if ($parentId === null && array_key_exists($parentKey, $this->data)) {
                $parentId = is_numeric($this->data[$parentKey]) ? (int) $this->data[$parentKey] : null;
            }

            if ($parentId !== null) {
                $query->where($parentKey, $parentId);
            }
        }

        if ($query->exists()) {
            $fail(trans('location::base.validation.check_exist_name'));
        }
    }
}
