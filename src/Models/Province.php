<?php

namespace JobMetric\Location\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * Class Province
 *
 * Represents a province (state/region) within a country. Provinces can have
 * cities and locations associated with them.
 *
 * @package JobMetric\Location
 *
 * @property int $id                    The primary identifier of the province row.
 * @property int $country_id            The owning country identifier.
 * @property string $name               The name of the province.
 * @property bool $status               Active flag (true=enabled, false=disabled).
 * @property Carbon|null $deleted_at    Soft delete timestamp.
 * @property Carbon $created_at         The timestamp when this province was created.
 * @property Carbon $updated_at         The timestamp when this province was last updated.
 *
 * @property-read Country $country
 * @property-read City[] $cities
 * @property-read Location[] $locations
 */
class Province extends Model
{
    use HasFactory, SoftDeletes, HasBooleanStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'name',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'country_id' => 'integer',
        'name'       => 'string',
        'status'     => 'boolean',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.province', parent::getTable());
    }

    /**
     * Get the owning country.
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Get cities belonging to this province.
     *
     * @return HasMany
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'province_id');
    }

    /**
     * Get locations associated with this province.
     *
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'province_id');
    }
}
