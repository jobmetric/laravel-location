<?php

namespace JobMetric\Location\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Location\Facades\City as CityFacade;
use JobMetric\Location\Models\City as CityModel;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\Province as ProvinceModel;
use Throwable;

class CityServiceTest extends ServiceTestCase
{
    /**
     * @throws Throwable
     */
    public function test_store_creates_city_under_province(): void
    {
        $province = ProvinceModel::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);

        $res = CityFacade::store([
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

        $province = ProvinceModel::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);

        CityFacade::store(['province_id' => $province->id, 'name' => 'Same']);
        CityFacade::store(['province_id' => $province->id, 'name' => 'Same']);
    }

    /**
     * @throws Throwable
     */
    public function test_store_same_name_in_different_provinces_is_allowed(): void
    {
        $p1 = ProvinceModel::factory()->create([
            'country_id' => Country::factory()
                ->create()->id,
        ]);
        $p2 = ProvinceModel::factory()->create([
            'country_id' => Country::factory()
                ->create()->id,
        ]);

        $r1 = CityFacade::store(['province_id' => $p1->id, 'name' => 'Same']);
        $r2 = CityFacade::store(['province_id' => $p2->id, 'name' => 'Same']);

        $this->assertTrue($r1->ok);
        $this->assertTrue($r2->ok);
    }

    /**
     * @throws Throwable
     */
    public function test_update_changes_name_with_scope_validation(): void
    {
        $province = ProvinceModel::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);
        $city = CityModel::factory()->create([
            'province_id' => $province->id,
            'name'        => 'Old',
        ]);

        $res = CityFacade::update($city->id, [
            'province_id' => $province->id,
            'name'        => 'New',
        ]);

        $this->assertTrue($res->ok);
        $this->assertDatabaseHas(config('location.tables.city'), [
            'id' => $city->id,
            'name' => 'New',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_destroy_restore_and_force_delete_cycle(): void
    {
        $graph = $this->makeLocationGraph();
        $city = $graph['city'];

        $destroy = CityFacade::destroy($city->id);
        $this->assertTrue($destroy->ok);
        $this->assertSoftDeleted(config('location.tables.city'), ['id' => $city->id]);

        $restore = CityFacade::restore($city->id);
        $this->assertTrue($restore->ok);

        $destroyAgain = CityFacade::destroy($city->id);
        $this->assertTrue($destroyAgain->ok);

        $force = CityFacade::forceDelete($city->id);
        $this->assertTrue($force->ok);
        $this->assertDatabaseMissing(config('location.tables.city'), ['id' => $city->id]);
    }
}
