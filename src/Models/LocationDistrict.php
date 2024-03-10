<?php

namespace JobMetric\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * @property int id
 * @property int location_country_id
 * @property int location_province_id
 * @property int location_city_id
 * @property string name
 * @property boolean status
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
}
