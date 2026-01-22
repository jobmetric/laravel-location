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
 * Class LocationRelation
 *
 * Represents a polymorphic relation between a location and any Eloquent model
 * that can have a location. This allows associating a specific location with
 * various entities (GeoArea, Address, Order, etc.) without altering their tables.
 *
 * @package JobMetric\Location
 *
 * @property int $location_id           The location identifier.
 * @property string $locationable_type  The class name of the related model.
 * @property int $locationable_id       The ID of the related model instance.
 * @property Carbon $created_at         The timestamp when this relation was created.
 *
 * @property-read Location $location
 * @property-read Model|MorphTo $locationable
 * @property-read mixed $locationable_resource
 *
 * @method static Builder|LocationRelation whereLocationId(int $location_id)
 * @method static Builder|LocationRelation whereLocationableType(string $locationable_type)
 * @method static Builder|LocationRelation whereLocationableId(int $locationable_id)
 * @method static Builder|LocationRelation forLocation(int $locationId)
 * @method static Builder|LocationRelation forLocationable(string $type, int $id)
 * @method static Builder|LocationRelation forModel(Model $model)
 */
class LocationRelation extends Pivot
{
    use HasFactory, HasMorphResourceAttributes;

    /**
     * This table does not have Laravel's updated_at column.
     *
     * @var bool
     */
    public const UPDATED_AT = null;

    /**
     * Touch the parent location when this relation changes.
     *
     * @var array<int, string>
     */
    protected $touches = ['location'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location_id',
        'locationable_type',
        'locationable_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'location_id'       => 'integer',
        'locationable_type' => 'string',
        'locationable_id'   => 'integer',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.location_relation', parent::getTable());
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
     * Get the related location.
     *
     * @return BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Get the related model (polymorphic).
     *
     * @return MorphTo
     */
    public function locationable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: filter by location id.
     *
     * @param Builder $query
     * @param int $locationId
     *
     * @return Builder
     */
    public function scopeForLocation(Builder $query, int $locationId): Builder
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope: filter by locationable pair.
     *
     * @param Builder $query
     * @param string $type
     * @param int $id
     *
     * @return Builder
     */
    public function scopeForLocationable(Builder $query, string $type, int $id): Builder
    {
        return $query->where([
            'locationable_type' => $type,
            'locationable_id'   => $id,
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
        return $this->scopeForLocationable($query, $model->getMorphClass(), $model->getKey());
    }
}
