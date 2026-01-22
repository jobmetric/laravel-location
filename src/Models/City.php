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
 * Class City
 *
 * Represents a city within a province. Cities can have districts
 * and locations associated with them.
 *
 * @package JobMetric\Location
 *
 * @property int $id                    The primary identifier of the city row.
 * @property int $province_id           The owning province identifier.
 * @property string $name               The name of the city.
 * @property bool $status               Active flag (true=enabled, false=disabled).
 * @property Carbon|null $deleted_at    Soft delete timestamp.
 * @property Carbon $created_at         The timestamp when this city was created.
 * @property Carbon $updated_at         The timestamp when this city was last updated.
 *
 * @property-read Province $province
 * @property-read District[] $districts
 * @property-read Location[] $locations
 */
class City extends Model
{
    use HasFactory, SoftDeletes, HasBooleanStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'province_id',
        'name',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'province_id' => 'integer',
        'name'        => 'string',
        'status'      => 'boolean',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.city', parent::getTable());
    }

    /**
     * Get the owning province.
     *
     * @return BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /**
     * Get districts belonging to this city.
     *
     * @return HasMany
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'city_id');
    }

    /**
     * Get locations associated with this city.
     *
     * @return HasMany
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'city_id');
    }
}
