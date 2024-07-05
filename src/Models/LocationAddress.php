<?php

namespace JobMetric\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\Barcode\Events\BarcodeableResourceEvent;
use JobMetric\Location\Events\Address\AddressableResourceEvent;

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
 * @property array info
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
    use HasFactory, SoftDeletes;

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
        'lng',
        'info'
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
        'lng' => 'string',
        'info' => 'array'
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

    public function getBarcodeableResourceAttribute()
    {
        $event = new AddressableResourceEvent($this->addressable);
        event($event);

        return $event->resource;
    }

    public function getCountryNameAttribute(): string
    {
        return $this->country->name;
    }

    public function getProvinceNameAttribute(): string
    {
        return $this->province->name;
    }

    public function getCityNameAttribute(): string
    {
        return $this->city->name;
    }

    public function getDistrictNameAttribute(): string
    {
        return $this->district->name;
    }

    public function getFullAddressAttribute(): string
    {
        return $this->pluck . ' ' . $this->unit . ' ' . $this->address . ' ' . $this->district->name . ' ' . $this->city->name . ' ' . $this->province->name . ' ' . $this->country->name . ' ' . $this->postcode;
    }
}
