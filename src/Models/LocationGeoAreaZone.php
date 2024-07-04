<?php

namespace JobMetric\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * table properties
 * @property int location_geo_area_id
 * @property int location_country_id
 * @property int location_province_id
 * @property int location_city_id
 * @property int location_district_id
 *
 * relationships properties
 * @property LocationGeoArea geoArea
 * @property LocationCountry country
 * @property LocationProvince province
 * @property LocationCity city
 * @property LocationDistrict district
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
 *
 * @property string district_name
 * @property boolean district_status
 *
 * @property string geo_area_name
 * @property boolean geo_area_status
 */
class LocationGeoAreaZone extends Pivot
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'location_geo_area_id',
        'location_country_id',
        'location_province_id',
        'location_city_id',
        'location_district_id'
    ];

    protected $casts = [
        'location_geo_area_id' => 'integer',
        'location_country_id' => 'integer',
        'location_province_id' => 'integer',
        'location_city_id' => 'integer',
        'location_district_id' => 'integer'
    ];

    public function getTable()
    {
        return config('location.tables.geo_area_zone', parent::getTable());
    }

    public function geoArea(): BelongsTo
    {
        return $this->belongsTo(LocationGeoArea::class, 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(LocationCountry::class, 'id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(LocationProvince::class, 'id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(LocationCity::class, 'id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(LocationDistrict::class, 'id');
    }

    public function getCountryNameAttribute(): string
    {
        return $this->country->name;
    }

    public function getCountryMobilePrefixAttribute(): string
    {
        return $this->country->mobile_prefix;
    }

    public function getCountryFlagAttribute(): string
    {
        return $this->country->flag;
    }

    public function getCountryValidationAttribute(): array
    {
        return $this->country->validation;
    }

    public function getCountryStatusAttribute(): bool
    {
        return $this->country->status;
    }

    public function getProvinceNameAttribute(): string
    {
        return $this->province->name;
    }

    public function getProvinceStatusAttribute(): bool
    {
        return $this->province->status;
    }

    public function getCityNameAttribute(): string
    {
        return $this->city->name;
    }

    public function getCityStatusAttribute(): bool
    {
        return $this->city->status;
    }

    public function getDistrictNameAttribute(): string
    {
        return $this->district->name;
    }

    public function getDistrictStatusAttribute(): bool
    {
        return $this->district->status;
    }

    public function getGeoAreaNameAttribute(): string
    {
        return $this->geoArea->name;
    }

    public function getGeoAreaStatusAttribute(): bool
    {
        return $this->geoArea->status;
    }
}
