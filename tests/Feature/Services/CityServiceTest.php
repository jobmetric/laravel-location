<?php

namespace JobMetric\Location\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Location\Models\City as CityModel;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\Province as ProvinceModel;
use JobMetric\Location\Services\City as CityService;
use Throwable;

class CityServiceTest extends ServiceTestCase
{
    /**
     * @throws Throwable
     */
    public function test_store_creates_city_under_province(): void
    {
        $service = app(CityService::class);
        $province = ProvinceModel::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);

        $res = $service->store([
            'province_id' => $province->id,
            'name'        => 'Tehran City',
            'status'      => true,
        ]);

        $this->assertTrue($res->ok);
        $this->assertDatabaseHas(config('location.tables.city'), [
            'province_id' => $province->id,
            'name'        => 'Tehran City',
        ]);
    }

    public function test_store_duplicate_name_in_same_province_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $service = app(CityService::class);
        $province = ProvinceModel::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);

        $service->store(['province_id' => $province->id, 'name' => 'Same']);
        $service->store(['province_id' => $province->id, 'name' => 'Same']);
    }

    /**
     * @throws Throwable
     */
    public function test_store_same_name_in_different_provinces_is_allowed(): void
    {
        $service = app(CityService::class);

        $p1 = ProvinceModel::factory()->create(['country_id' => Country::factory()->create()->id]);
        $p2 = ProvinceModel::factory()->create(['country_id' => Country::factory()->create()->id]);

        $r1 = $service->store(['province_id' => $p1->id, 'name' => 'Same']);
        $r2 = $service->store(['province_id' => $p2->id, 'name' => 'Same']);

        $this->assertTrue($r1->ok);
        $this->assertTrue($r2->ok);
    }

    /**
     * @throws Throwable
     */
    public function test_update_changes_name_with_scope_validation(): void
    {
        $service = app(CityService::class);

        $province = ProvinceModel::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);
        $city = CityModel::factory()->create([
            'province_id' => $province->id,
            'name'        => 'Old',
        ]);

        $res = $service->update($city->id, [
            'province_id' => $province->id,
            'name'        => 'New',
        ]);

        $this->assertTrue($res->ok);
        $this->assertDatabaseHas(config('location.tables.city'), [
            'id'   => $city->id,
            'name' => 'New',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_destroy_restore_and_force_delete_cycle(): void
    {
        $service = app(CityService::class);

        $graph = $this->makeLocationGraph();
        $city = $graph['city'];

        $destroy = $service->destroy($city->id);
        $this->assertTrue($destroy->ok);
        $this->assertSoftDeleted(config('location.tables.city'), ['id' => $city->id]);

        $restore = $service->restore($city->id);
        $this->assertTrue($restore->ok);

        $destroyAgain = $service->destroy($city->id);
        $this->assertTrue($destroyAgain->ok);

        $force = $service->forceDelete($city->id);
        $this->assertTrue($force->ok);
        $this->assertDatabaseMissing(config('location.tables.city'), ['id' => $city->id]);
    }
}

