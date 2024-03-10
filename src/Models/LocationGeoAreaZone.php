<?php

namespace JobMetric\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * @property int geo_area_id
 * @property int location_country_id
 * @property int location_province_id
 * @property int location_city_id
 * @property int location_district_id
 */
class LocationGeoAreaZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'geo_area_id',
        'location_country_id',
        'location_province_id',
        'location_city_id',
        'location_district_id'
    ];

    protected $casts = [
        'geo_area_id' => 'integer',
        'location_country_id' => 'integer',
        'location_province_id' => 'integer',
        'location_city_id' => 'integer',
        'location_district_id' => 'integer'
    ];

    public function getTable()
    {
        return config('location.tables.geo_area_zone', parent::getTable());
    }
}
