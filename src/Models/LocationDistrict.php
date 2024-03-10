<?php

namespace JobMetric\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * table properties
 * @property int id
 * @property int location_country_id
 * @property int location_province_id
 * @property int location_city_id
 * @property string name
 * @property boolean status
 *
 * relationships properties
 * @property LocationCountry country
 * @property LocationProvince province
 * @property LocationCity city
 * @property LocationGeoAreaZone[] geoAreaZones
 * @property LocationGeoAreaZone[] geoAreaZonesWithGeoArea
 * @property LocationAddress[] addresses
 *
 * @property string country_name
 * @property string country_mobile_prefix
 * @property string country_flag
 * @property string country_validation
 * @property boolean country_status
 *
 * @property string province_name
 * @property boolean province_status
 *
 * @property string city_name
 * @property boolean city_status
 */
class LocationDistrict extends Model
{
    use HasFactory, SoftDeletes, HasBooleanStatus;

    protected $fillable = [
        'location_country_id',
        'location_province_id',
        'location_city_id',
        'name',
        'status'
    ];

    protected $casts = [
        'location_country_id' => 'integer',
        'location_province_id' => 'integer',
        'location_city_id' => 'integer',
        'name' => 'string',
        'status' => 'boolean'
    ];

    public function getTable()
    {
        return config('location.tables.district', parent::getTable());
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

    public function geoAreaZones(): HasMany
    {
        return $this->hasMany(LocationGeoAreaZone::class);
    }

    public function geoAreaZonesWithGeoArea(): HasMany
    {
        return $this->hasMany(LocationGeoAreaZone::class)->with('geoArea');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(LocationAddress::class);
    }

    public function getCountryNameAttribute()
    {
        return $this->country->name;
    }

    public function getCountryMobilePrefixAttribute()
    {
        return $this->country->mobile_prefix;
    }

    public function getCountryFlagAttribute()
    {
        return $this->country->flag;
    }

    public function getCountryValidationAttribute()
    {
        return $this->country->validation;
    }

    public function getCountryStatusAttribute()
    {
        return $this->country->status;
    }

    public function getProvinceNameAttribute()
    {
        return $this->province->name;
    }

    public function getProvinceStatusAttribute()
    {
        return $this->province->status;
    }

    public function getCityNameAttribute()
    {
        return $this->city->name;
    }

    public function getCityStatusAttribute()
    {
        return $this->city->status;
    }
}
