<?php

namespace JobMetric\Location\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use JobMetric\PackageCore\Traits\HasMorphResourceAttributes;

/**
 * Class GeoAreaRelation
 *
 * Represents a polymorphic relation between a geo area and any Eloquent model
 * that can be associated with a geo area. This allows associating a specific
 * geo area with various entities (Order, Invoice, DeliveryZone, etc.).
 *
 * @package JobMetric\Location
 *
 * @property int $geo_area_id             The geo area identifier.
 * @property string $geographical_type    The class name of the related model.
 * @property int $geographical_id         The ID of the related model instance.
 * @property Carbon $created_at           The timestamp when this relation was created.
 *
 * @property-read GeoArea $geoArea
 * @property-read Model|MorphTo $geographical
 * @property-read mixed $geographical_resource
 *
 * @method static Builder|GeoAreaRelation whereGeoAreaId(int $geo_area_id)
 * @method static Builder|GeoAreaRelation whereGeographicalType(string $geographical_type)
 * @method static Builder|GeoAreaRelation whereGeographicalId(int $geographical_id)
 * @method static Builder|GeoAreaRelation forGeoArea(int $geoAreaId)
 * @method static Builder|GeoAreaRelation forGeographical(string $type, int $id)
 * @method static Builder|GeoAreaRelation forModel(Model $model)
 */
class GeoAreaRelation extends Pivot
{
    use HasFactory, HasMorphResourceAttributes;

    /**
     * This table does not have Laravel's updated_at column.
     *
     * @var bool
     */
    public const UPDATED_AT = null;

    /**
     * Touch the parent geo area when this relation changes.
     *
     * @var array<int, string>
     */
    protected $touches = ['geoArea'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'geo_area_id',
        'geographical_type',
        'geographical_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'geo_area_id'       => 'integer',
        'geographical_type' => 'string',
        'geographical_id'   => 'integer',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.geo_area_relation', parent::getTable());
    }

    /**
     * Initialize model events.
     *
     * @return void
     */
    protected static function booted(): void
    {
        // Ensure created_at is set by application layer as well (DB has default too).
        static::creating(function (self $relation): void {
            if (empty($relation->created_at)) {
                $relation->created_at = now();
            }
        });
    }

    /**
     * Get the related geo area.
     *
     * @return BelongsTo
     */
    public function geoArea(): BelongsTo
    {
        return $this->belongsTo(GeoArea::class, 'geo_area_id');
    }

    /**
     * Get the related model (polymorphic).
     *
     * @return MorphTo
     */
    public function geographical(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: filter by geo area id.
     *
     * @param Builder $query
     * @param int $geoAreaId
     *
     * @return Builder
     */
    public function scopeForGeoArea(Builder $query, int $geoAreaId): Builder
    {
        return $query->where('geo_area_id', $geoAreaId);
    }

    /**
     * Scope: filter by geographical pair.
     *
     * @param Builder $query
     * @param string $type
     * @param int $id
     *
     * @return Builder
     */
    public function scopeForGeographical(Builder $query, string $type, int $id): Builder
    {
        return $query->where([
            'geographical_type' => $type,
            'geographical_id'   => $id,
        ]);
    }

    /**
     * Scope: filter by a concrete Eloquent model instance.
     *
     * @param Builder $query
     * @param Model $model
     *
     * @return Builder
     */
    public function scopeForModel(Builder $query, Model $model): Builder
    {
        return $this->scopeForGeographical($query, $model->getMorphClass(), $model->getKey());
    }
}
