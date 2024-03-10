<?php

namespace JobMetric\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * @property int id
 * @property string name
 * @property string flag
 * @property string mobile_prefix
 * @property array validation
 * @property boolean status
 */
class LocationCountry extends Model
{
    use HasFactory, HasBooleanStatus;

    protected $fillable = [
        'name',
        'flag',
        'mobile_prefix',
        'validation',
        'status'
    ];

    protected $casts = [
        'name' => 'string',
        'flag' => 'string',
        'mobile_prefix' => 'string',
        'validation' => 'array',
        'status' => 'boolean'
    ];

    public function getTable()
    {
        return config('location.tables.country', parent::getTable());
    }
}
