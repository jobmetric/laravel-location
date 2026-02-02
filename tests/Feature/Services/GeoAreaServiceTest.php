<?php

namespace JobMetric\Location\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Location\Models\GeoArea as GeoAreaModel;
use JobMetric\Location\Models\LocationRelation;
use JobMetric\Location\Services\GeoArea as GeoAreaService;
use Throwable;

class GeoAreaServiceTest extends ServiceTestCase
{
    /**
     * @throws Throwable
     */
    public function test_store_creates_geo_area_with_locations(): void
    {
        $service = app(GeoAreaService::class);
        $graph = $this->makeLocationGraph();

        $res = $service->store([
            'translation' => [
                'en' => [
                    'name'        => 'Test Area',
                    'description' => 'Desc',
                ],
            ],
            'status'      => true,
            'locations'   => [
                [
                    'country_id'  => $graph['country']->id,
                    'province_id' => $graph['province']->id,
                    'city_id'     => $graph['city']->id,
                    'district_id' => $graph['district']->id,
                ],
            ],
        ]);

        $this->assertTrue($res->ok);
        $this->assertEquals(201, $res->status);

        $geoArea = GeoAreaModel::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas(config('location.tables.geo_area'), [
            'id' => $geoArea->id,
        ]);

        $this->assertDatabaseHas(config('location.tables.location_relation'), [
            'locationable_type' => GeoAreaModel::class,
            'locationable_id'   => $geoArea->id,
        ]);
    }

    /**
     * Request validation should reject duplicate location entries.
     *
     * @throws Throwable
     */
    public function test_store_duplicate_locations_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $service = app(GeoAreaService::class);
        $graph = $this->makeLocationGraph();

        $location = [
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'district_id' => $graph['district']->id,
        ];

        $service->store([
            'translation' => [
                'en' => [
                    'name'        => 'Test Area',
                    'description' => 'Desc',
                ],
            ],
            'locations'   => [$location, $location],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_update_syncs_locations(): void
    {
        $service = app(GeoAreaService::class);
        $g1 = $this->makeLocationGraph();
        $g2 = $this->makeLocationGraph();

        $service->store([
            'translation' => [
                'en' => ['name' => 'Area', 'description' => 'Desc'],
            ],
            'locations'   => [
                [
                    'country_id'  => $g1['country']->id,
                    'province_id' => $g1['province']->id,
                    'city_id'     => $g1['city']->id,
                    'district_id' => $g1['district']->id,
                ],
            ],
        ]);

        $geoArea = GeoAreaModel::query()->latest('id')->firstOrFail();

        $before = LocationRelation::query()
            ->where('locationable_type', GeoAreaModel::class)
            ->where('locationable_id', $geoArea->id)
            ->count();
        $this->assertEquals(1, $before);

        $res = $service->update($geoArea->id, [
            'translation' => [
                'en' => ['name' => 'Area2', 'description' => 'Desc2'],
            ],
            'locations'   => [
                [
                    'country_id'  => $g2['country']->id,
                    'province_id' => $g2['province']->id,
                    'city_id'     => $g2['city']->id,
                    'district_id' => $g2['district']->id,
                ],
            ],
        ]);

        $this->assertTrue($res->ok);

        $after = LocationRelation::query()
            ->where('locationable_type', GeoAreaModel::class)
            ->where('locationable_id', $geoArea->id)
            ->count();
        $this->assertEquals(1, $after);
    }

    /**
     * @throws Throwable
     */
    public function test_destroy_restore_and_force_delete_cycle(): void
    {
        $service = app(GeoAreaService::class);
        $graph = $this->makeLocationGraph();

        $service->store([
            'translation' => [
                'en' => ['name' => 'Area', 'description' => 'Desc'],
            ],
            'locations'   => [
                [
                    'country_id' => $graph['country']->id,
                ],
            ],
        ]);

        $geoArea = GeoAreaModel::query()->latest('id')->firstOrFail();

        $destroy = $service->destroy($geoArea->id);
        $this->assertTrue($destroy->ok);
        $this->assertSoftDeleted(config('location.tables.geo_area'), ['id' => $geoArea->id]);

        $restore = $service->restore($geoArea->id);
        $this->assertTrue($restore->ok);

        $destroyAgain = $service->destroy($geoArea->id);
        $this->assertTrue($destroyAgain->ok);

        $force = $service->forceDelete($geoArea->id);
        $this->assertTrue($force->ok);
        $this->assertDatabaseMissing(config('location.tables.geo_area'), ['id' => $geoArea->id]);
    }
}

