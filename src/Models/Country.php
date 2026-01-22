<?php

namespace JobMetric\Location\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * Class Country
 *
 * Represents a country in the location system. Countries can have provinces
 * and locations associated with them.
 *
 * @package JobMetric\Location
 *
 * @property int $id                          The primary identifier of the country row.
 * @property string $name                     The name of the country.
 * @property string|null $flag                Flag identifier for the country (filename).
 * @property string|null $mobile_prefix       International mobile prefix (country calling code).
 * @property array|null $validation           Mobile number validation rules (regex patterns).
 * @property string|null $address_on_letter   Address format template for printing.
 * @property bool $status                     Active flag (true=enabled, false=disabled).
 * @property Carbon|null $deleted_at          Soft delete timestamp.
 * @property Carbon $created_at               The timestamp when this country was created.
 * @property Carbon $updated_at               The timestamp when this country was last updated.
 *
 * @property-read Province[] $provinces
 * @property-read Location[] $locations
 */
class Country extends Model
{
    use HasFactory, SoftDeletes, HasBooleanStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'flag',
        'mobile_prefix',
        'validation',
        'address_on_letter',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'name'              => 'string',
        'flag'              => 'string',
        'mobile_prefix'     => 'string',
        'validation'        => AsArrayObject::class,
        'address_on_letter' => 'string',
        'status'            => 'boolean',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.country', parent::getTable());
    }

    /**
     * Get provinces belonging to this country.
     *
     * @return HasMany
     */
    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'country_id');
    }

    /**
     * Get locations associated with this country.
     *
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'country_id');
    }

    /**
     * Accessor: format mobile prefix with plus sign.
     *
     * @param mixed $value
     *
     * @return string|null
     */
    public function getMobilePrefixAttribute($value): ?string
    {
        return $value ? '+' . $value : null;
    }

    /**
     * Mutator: remove plus sign from mobile prefix before storing.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setMobilePrefixAttribute($value): void
    {
        $this->attributes['mobile_prefix'] = $value ? str_replace('+', '', $value) : null;
    }
}
