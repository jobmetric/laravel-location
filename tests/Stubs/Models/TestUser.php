<?php

namespace JobMetric\Location\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use JobMetric\Location\HasAddress;
use JobMetric\Location\HasGeoArea;

class TestUser extends Model
{
    use HasAddress;
    use HasGeoArea;

    protected $table = 'test_users';

    protected $guarded = [];
}

