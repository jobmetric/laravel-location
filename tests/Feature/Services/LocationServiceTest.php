<?php

namespace JobMetric\Location\Tests\Feature\Services;

use JobMetric\Location\Models\Location as LocationModel;
use JobMetric\Location\Services\Location as LocationService;
use Throwable;

class LocationServiceTest extends ServiceTestCase
{
    /**
     * @throws Throwable
     */
    public function test_store_creates_unique_location_and_second_call_returns_existing(): void
    {
        $service = app(LocationService::class);
        $graph = $this->makeLocationGraph();

        $payload = [
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'district_id' => $graph['district']->id,
        ];

        $res1 = $service->store($payload);
        $this->assertTrue($res1->ok);

        $countAfterFirst = LocationModel::query()->count();

        $res2 = $service->store($payload);
        $this->assertTrue($res2->ok);

        $this->assertEquals($countAfterFirst, LocationModel::query()->count());
        $this->assertDatabaseHas(config('location.tables.location'), $payload);
    }
}

