<?php

namespace JobMetric\Location\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Location\Models\City as CityModel;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\District as DistrictModel;
use JobMetric\Location\Models\Province;
use JobMetric\Location\Services\District as DistrictService;
use Throwable;

class DistrictServiceTest extends ServiceTestCase
{
    /**
     * @throws Throwable
     */
    public function test_store_creates_district_under_city(): void
    {
        $service = app(DistrictService::class);

        $city = CityModel::factory()->create([
            'province_id' => Province::factory()->create([
                'country_id' => Country::factory()->create()->id,
            ])->id,
        ]);

        $res = $service->store([
            'city_id' => $city->id,
            'name'    => 'District 1',
            'status'  => true,
        ]);

        $this->assertTrue($res->ok);
        $this->assertDatabaseHas(config('location.tables.district'), [
            'city_id' => $city->id,
            'name'    => 'District 1',
        ]);
    }

    public function test_store_duplicate_name_in_same_city_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $service = app(DistrictService::class);
        $graph = $this->makeLocationGraph();
        $city = $graph['city'];

        $service->store(['city_id' => $city->id, 'name' => 'Same']);
        $service->store(['city_id' => $city->id, 'name' => 'Same']);
    }

    /**
     * @throws Throwable
     */
    public function test_store_same_name_in_different_cities_is_allowed(): void
    {
        $service = app(DistrictService::class);
        $g1 = $this->makeLocationGraph();
        $g2 = $this->makeLocationGraph();

        $r1 = $service->store(['city_id' => $g1['city']->id, 'name' => 'Same']);
        $r2 = $service->store(['city_id' => $g2['city']->id, 'name' => 'Same']);

        $this->assertTrue($r1->ok);
        $this->assertTrue($r2->ok);
    }

    /**
     * @throws Throwable
     */
    public function test_update_changes_name_with_scope_validation(): void
    {
        $service = app(DistrictService::class);
        $graph = $this->makeLocationGraph();

        $district = DistrictModel::factory()->create([
            'city_id' => $graph['city']->id,
            'name'    => 'Old',
        ]);

        $res = $service->update($district->id, [
            'city_id' => $graph['city']->id,
            'name'    => 'New',
        ]);

        $this->assertTrue($res->ok);
        $this->assertDatabaseHas(config('location.tables.district'), [
            'id'   => $district->id,
            'name' => 'New',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_destroy_restore_and_force_delete_cycle(): void
    {
        $service = app(DistrictService::class);
        $graph = $this->makeLocationGraph();
        $district = $graph['district'];

        $destroy = $service->destroy($district->id);
        $this->assertTrue($destroy->ok);
        $this->assertSoftDeleted(config('location.tables.district'), ['id' => $district->id]);

        $restore = $service->restore($district->id);
        $this->assertTrue($restore->ok);

        $destroyAgain = $service->destroy($district->id);
        $this->assertTrue($destroyAgain->ok);

        $force = $service->forceDelete($district->id);
        $this->assertTrue($force->ok);
        $this->assertDatabaseMissing(config('location.tables.district'), ['id' => $district->id]);
    }
}

