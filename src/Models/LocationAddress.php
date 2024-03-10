<?php

namespace JobMetric\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
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
}
