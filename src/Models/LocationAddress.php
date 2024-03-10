<?php

namespace JobMetric\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * table properties
 * @property int id
 * @property string addressable_type
 * @property int addressable_id
 * @property int location_country_id
 * @property int location_province_id
 * @property int location_city_id
 * @property int location_district_id
 * @property string address
 * @property string pluck
 * @property string unit
 * @property string postcode
 * @property string lat
 * @property string lng
 *
 * relationships properties
 * @property LocationCountry country
 * @property LocationProvince province
 * @property LocationCity city
 * @property LocationDistrict district
 *
 * @property string country_name
 * @property string province_name
 * @property string city_name
 * @property string district_name
 *
 * @property string full_address
 */
class LocationAddress extends Model
{
    use HasFactory, SoftDeletes, HasBooleanStatus;

    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'location_country_id',
        'location_province_id',
        'location_city_id',
        'location_district_id',
        'address',
        'pluck',
        'unit',
        'postcode',
        'lat',
        'lng'
    ];

    protected $casts = [
        'addressable_type' => 'string',
        'addressable_id' => 'integer',
        'location_country_id' => 'integer',
        'location_province_id' => 'integer',
        'location_city_id' => 'integer',
        'location_district_id' => 'integer',
        'address' => 'string',
        'pluck' => 'string',
        'unit' => 'string',
        'postcode' => 'string',
        'lat' => 'string',
        'lng' => 'string'
    ];

    public function getTable()
    {
        return config('location.tables.address', parent::getTable());
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(LocationCountry::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(LocationProvince::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(LocationCity::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(LocationDistrict::class);
    }

    public function getCountryNameAttribute()
    {
        return $this->country->name;
    }

    public function getProvinceNameAttribute()
    {
        return $this->province->name;
    }

    public function getCityNameAttribute()
    {
        return $this->city->name;
    }

    public function getDistrictNameAttribute()
    {
        return $this->district->name;
    }

    public function getFullAddressAttribute()
    {
        return $this->pluck . ' ' . $this->unit . ' ' . $this->address . ' ' . $this->district->name . ' ' . $this->city->name . ' ' . $this->province->name . ' ' . $this->country->name . ' ' . $this->postcode;
    }
}
