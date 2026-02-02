<?php

namespace JobMetric\Location\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Location
 *
 * Represents a unique combination of country, province, city, and district.
 * This model ensures that each unique location combination is stored only once
 * and can be referenced by multiple entities through the location_relation table.
 *
 * @package JobMetric\Location
 *
 * @property int $id                    The primary identifier of the location row.
 * @property int $country_id            The country identifier (required).
 * @property int|null $province_id      The province identifier (optional).
 * @property int|null $city_id          The city identifier (optional).
 * @property int|null $district_id      The district identifier (optional).
 * @property Carbon $created_at         The timestamp when this location was created.
 *
 * @property-read Country $country
 * @property-read Province|null $province
 * @property-read City|null $city
 * @property-read District|null $district
 * @property-read LocationRelation[] $locationRelations
 *
 * @method static Builder|Location whereCountryId(int $country_id)
 * @method static Builder|Location whereProvinceId(?int $province_id)
 * @method static Builder|Location whereCityId(?int $city_id)
 * @method static Builder|Location whereDistrictId(?int $district_id)
 */
class Location extends Model
{
    use HasFactory;

    /**
     * This table does not have Laravel's updated_at column.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'province_id',
        'city_id',
        'district_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'country_id'  => 'integer',
        'province_id' => 'integer',
        'city_id'     => 'integer',
        'district_id' => 'integer',
        'created_at'  => 'datetime',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('location.tables.location', parent::getTable());
    }

    /**
     * Get the related country.
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Get the related province.
     *
     * @return BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /**
     * Get the related city.
     *
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Get the related district.
     *
     * @return BelongsTo
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * Get location relations associated with this location.
     *
     * @return HasMany
     */
    public function locationRelations(): HasMany
    {
        return $this->hasMany(LocationRelation::class, 'location_id');
    }
}
