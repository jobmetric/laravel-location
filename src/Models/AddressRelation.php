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
 * Class AddressRelation
 *
 * Represents a polymorphic relation between an address and any Eloquent model
 * that can use an address. This allows associating a specific address with
 * various entities (Order, Invoice, Shipment, etc.) with optional collection
 * categorization.
 *
 * @package JobMetric\Location
 *
 * @property int $address_id              The address identifier.
 * @property string $addressable_type     The class name of the related model.
 * @property int $addressable_id          The ID of the related model instance.
 * @property string|null $collection      Collection name for categorizing addresses.
 * @property Carbon $created_at           The timestamp when this relation was created.
 *
 * @property-read Address $address
 * @property-read Model|MorphTo $addressable
 * @property-read mixed $addressable_resource
 *
 * @method static Builder|AddressRelation whereAddressId(int $address_id)
 * @method static Builder|AddressRelation whereAddressableType(string $addressable_type)
 * @method static Builder|AddressRelation whereAddressableId(int $addressable_id)
 * @method static Builder|AddressRelation whereCollection(?string $collection)
 * @method static Builder|AddressRelation forAddress(int $addressId)
 * @method static Builder|AddressRelation forAddressable(string $type, int $id)
 * @method static Builder|AddressRelation forModel(Model $model)
 */
class AddressRelation extends Pivot
{
    use HasFactory, HasMorphResourceAttributes;

    /**
     * This table does not have Laravel's updated_at column.
     *
     * @var bool
     */
    public const UPDATED_AT = null;

    /**
     * Touch the parent address when this relation changes.
     *
     * @var array<int, string>
     */
    protected $touches = ['address'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'address_id',
        'addressable_type',
        'addressable_id',
        'collection',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'address_id'       => 'integer',
        'addressable_type' => 'string',
        'addressable_id'   => 'integer',
        'collection'       => 'string',
        'created_at'       => 'datetime',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.address_relation', parent::getTable());
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
     * Get the related address.
     *
     * @return BelongsTo
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Get the related model (polymorphic).
     *
     * @return MorphTo
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: filter by address id.
     *
     * @param Builder $query
     * @param int $addressId
     *
     * @return Builder
     */
    public function scopeForAddress(Builder $query, int $addressId): Builder
    {
        return $query->where('address_id', $addressId);
    }

    /**
     * Scope: filter by addressable pair.
     *
     * @param Builder $query
     * @param string $type
     * @param int $id
     *
     * @return Builder
     */
    public function scopeForAddressable(Builder $query, string $type, int $id): Builder
    {
        return $query->where([
            'addressable_type' => $type,
            'addressable_id'   => $id,
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
        return $this->scopeForAddressable($query, $model->getMorphClass(), $model->getKey());
    }
}
