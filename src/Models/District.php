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
 * Class District
 *
 * Represents a district within a city. Districts can have locations
 * associated with them.
 *
 * @package JobMetric\Location
 *
 * @property int $id                    The primary identifier of the district row.
 * @property int $city_id               The owning city identifier.
 * @property string $name               The name of the district.
 * @property bool $status               Active flag (true=enabled, false=disabled).
 * @property Carbon|null $deleted_at    Soft delete timestamp.
 * @property Carbon $created_at         The timestamp when this district was created.
 * @property Carbon $updated_at         The timestamp when this district was last updated.
 *
 * @property-read City $city
 * @property-read Location[] $locations
 */
class District extends Model
{
    use HasFactory, SoftDeletes, HasBooleanStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'city_id',
        'name',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'city_id' => 'integer',
        'name'    => 'string',
        'status'  => 'boolean',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.district', parent::getTable());
    }

    /**
     * Get the owning city.
     *
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get locations associated with this district.
     *
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'district_id');
    }
}
