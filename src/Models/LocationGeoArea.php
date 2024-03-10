<?php

namespace JobMetric\Location\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property boolean status
 */
class LocationGeoArea extends Model
{
    use HasFactory, SoftDeletes, HasBooleanStatus;

    protected $fillable = [
        'title',
        'description',
        'status'
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'status' => 'boolean'
    ];

    public function getTable()
    {
        return config('location.tables.geo_area', parent::getTable());
    }
}
