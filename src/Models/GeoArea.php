<?php

namespace JobMetric\Location\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\PackageCore\Models\HasBooleanStatus;
use JobMetric\Translation\HasTranslation;

/**
 * Class GeoArea
 *
 * Represents a geographical area that can span multiple locations
 * (country, province, city, district). Used for defining delivery zones,
 * service areas, or other geographic boundaries.
 *
 * @package JobMetric\Location
 *
 * @property int $id                    The primary identifier of the geo area row.
 * @property bool $status               Active flag (true=enabled, false=disabled).
 * @property Carbon|null $deleted_at    Soft delete timestamp.
 * @property Carbon $created_at         The timestamp when this geo area was created.
 * @property Carbon $updated_at         The timestamp when this geo area was last updated.
 *
 * @property-read GeoAreaRelation[] $geoAreaRelations
 */
class GeoArea extends Model
{
    use HasFactory, SoftDeletes, HasBooleanStatus, HasTranslation;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array<int, string>
     */
    protected array $translatables = [
        'name',
        'description',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.geo_area', parent::getTable());
    }

    /**
     * Get geo area relations associated with this geo area.
     *
     * @return HasMany
     */
    public function geoAreaRelations(): HasMany
    {
        return $this->hasMany(GeoAreaRelation::class, 'geo_area_id');
    }
}
