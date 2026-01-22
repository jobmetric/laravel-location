<?php

namespace JobMetric\Location\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Address
 *
 * Represents a physical address with location details, coordinates, and
 * additional information. Addresses can have a parent address (for hierarchical
 * structures) and belong to any model via polymorphic relation.
 *
 * @package JobMetric\Location
 *
 * @property int $id                    The primary identifier of the address row.
 * @property int|null $parent_id        The parent address identifier (for hierarchical addresses).
 * @property string $owner_type         The class name of the owning model.
 * @property int $owner_id              The ID of the owning model instance.
 * @property object|null $address       Address details stored as JSON (AsArrayObject).
 * @property string|null $postcode      Postal/ZIP code of the address.
 * @property string|null $lat           Latitude coordinate (decimal degrees).
 * @property string|null $lng           Longitude coordinate (decimal degrees).
 * @property array|null $info           Additional address information (JSON).
 * @property Carbon|null $deleted_at    Soft delete timestamp.
 * @property Carbon $created_at         The timestamp when this address was created.
 *
 * @property-read Address|null $parent
 * @property-read Address|null $child
 * @property-read Model|MorphTo $owner
 * @property-read AddressRelation[] $addressRelations
 * @property-read LocationRelation|null $locationRelation
 * @property-read Location|null $location
 * @property-read Country|null $country
 * @property-read Province|null $province
 * @property-read City|null $city
 * @property-read District|null $district
 * @property-read string $full_address
 * @property-read string $address_for_letter
 */
class Address extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * This table does not have Laravel's updated_at column.
     *
     * @var bool
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'owner_type',
        'owner_id',
        'address',
        'postcode',
        'lat',
        'lng',
        'info',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parent_id'  => 'integer',
        'owner_type' => 'string',
        'owner_id'   => 'integer',
        'address'    => AsArrayObject::class,
        'postcode'   => 'string',
        'lat'        => 'string',
        'lng'        => 'string',
        'info'       => AsArrayObject::class,
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.address', parent::getTable());
    }

    /**
     * Get the parent address (if this is a child address).
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child address (if this address has a child).
     *
     * @return HasOne
     */
    public function child(): HasOne
    {
        return $this->hasOne(self::class, 'parent_id');
    }

    /**
     * Get the child address recursively (with nested children).
     *
     * @return HasOne
     */
    public function childRecursive(): HasOne
    {
        return $this->child()->with('childRecursive');
    }

    /**
     * Get the owning model (polymorphic).
     *
     * @return MorphTo
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get address relations associated with this address.
     *
     * @return HasMany
     */
    public function addressRelations(): HasMany
    {
        return $this->hasMany(AddressRelation::class, 'address_id');
    }

    /**
     * Get location relation for this address (polymorphic).
     *
     * @return MorphOne
     */
    public function locationRelation(): MorphOne
    {
        return $this->morphOne(LocationRelation::class, 'locationable');
    }

    /**
     * Get location through location relation.
     *
     * @return Location|null
     */
    public function getLocationAttribute(): ?Location
    {
        return $this->locationRelation?->location;
    }

    /**
     * Get country through location.
     *
     * @return Country|null
     */
    public function getCountryAttribute(): ?Country
    {
        return $this->location?->country;
    }

    /**
     * Get province through location.
     *
     * @return Province|null
     */
    public function getProvinceAttribute(): ?Province
    {
        return $this->location?->province;
    }

    /**
     * Get city through location.
     *
     * @return City|null
     */
    public function getCityAttribute(): ?City
    {
        return $this->location?->city;
    }

    /**
     * Get district through location.
     *
     * @return District|null
     */
    public function getDistrictAttribute(): ?District
    {
        return $this->location?->district;
    }

    /**
     * Accessor: get formatted full address string.
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        return $this->getAddressForLetterAttribute();
    }

    /**
     * Accessor: get formatted address string for letter printing.
     *
     * Uses the country's address_on_letter template to format the complete address.
     *
     * @return string
     */
    public function getAddressForLetterAttribute(): string
    {
        $country = $this->country->name;
        $province = $this->province->name;
        $city = $this->city->name;
        $district = $this?->district->name;
        $blvd = $this?->address?->blvd;
        $street = $this?->address?->street;
        $alley = $this?->address?->alley;
        $number = $this?->address?->number;
        $floor = $this?->address?->floor;
        $unit = $this?->address?->unit;
        $receiver_mobile_prefix = $this?->info?->mobile_prefix;
        $receiver_mobile = $this?->info?->mobile;
        $receiver_number = $receiver_mobile_prefix . $receiver_mobile;
        $receiver_name = $this?->info?->name;
        $postcode = $this?->postcode;
        $address = $this->country?->address_on_letter ?? "{country}, {province}, {city}\n{district}, {blvd}, {street}, {alley}\n No: {number} Flore: {floor} Unit: {unit}\nPostcode: {postcode}\nReceiver: {receiver_name}\nPhone: {receiver_number}";

        $address = str_replace('{country}', $country, $address);
        $address = str_replace('{province}', $province, $address);
        $address = str_replace('{city}', $city, $address);
        $address = str_replace('{district}', $district, $address);
        $address = str_replace('{blvd}', $blvd, $address);
        $address = str_replace('{street}', $street, $address);
        $address = str_replace('{alley}', $alley, $address);
        $address = str_replace('{number}', $number, $address);
        $address = str_replace('{floor}', $floor, $address);
        $address = str_replace('{unit}', $unit, $address);
        $address = str_replace('{receiver_number}', $receiver_number, $address);
        $address = str_replace('{receiver_name}', $receiver_name, $address);

        return str_replace('{postcode}', $postcode, $address);
    }
}
