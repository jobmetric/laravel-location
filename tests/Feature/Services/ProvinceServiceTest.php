<?php

namespace JobMetric\Location\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Location\Facades\Province as ProvinceFacade;
use JobMetric\Location\Models\Country as CountryModel;
use JobMetric\Location\Models\Province as ProvinceModel;
use Throwable;

class ProvinceServiceTest extends ServiceTestCase
{
    /**
     * @throws Throwable
     */
    public function test_store_creates_province_under_country(): void
    {
        $country = CountryModel::factory()->create();

        $res = ProvinceFacade::store([
            'country_id' => $country->id,
            'name'       => 'Tehran',
            'status'     => true,
        ]);

        $this->assertTrue($res->ok);
        $this->assertDatabaseHas(config('location.tables.province'), [
            'country_id' => $country->id,
            'name'       => 'Tehran',
        ]);
    }

    /**
     * Same name under same country must be unique.
     */
    public function test_store_duplicate_name_in_same_country_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $country = CountryModel::factory()->create();

        ProvinceFacade::store(['country_id' => $country->id, 'name' => 'Tehran']);
        ProvinceFacade::store(['country_id' => $country->id, 'name' => 'Tehran']);
    }

    /**
     * Same name in different countries is allowed.
     *
     * @throws Throwable
     */
    public function test_store_same_name_in_different_countries_is_allowed(): void
    {
        $c1 = CountryModel::factory()->create();
        $c2 = CountryModel::factory()->create();

        $r1 = ProvinceFacade::store(['country_id' => $c1->id, 'name' => 'Tehran']);
        $r2 = ProvinceFacade::store(['country_id' => $c2->id, 'name' => 'Tehran']);

        $this->assertTrue($r1->ok);
        $this->assertTrue($r2->ok);
    }

    /**
     * @throws Throwable
     */
    public function test_update_changes_name_with_scope_validation(): void
    {
        $country = CountryModel::factory()->create();
        $province = ProvinceModel::factory()->create([
            'country_id' => $country->id,
            'name'       => 'Old',
        ]);

        $res = ProvinceFacade::update($province->id, [
            'country_id' => $country->id,
            'name'       => 'New',
        ]);

        $this->assertTrue($res->ok);
        $this->assertDatabaseHas(config('location.tables.province'), [
            'id'   => $province->id,
            'name' => 'New',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_destroy_restore_and_force_delete_cycle(): void
    {
        $province = ProvinceModel::factory()->create([
            'country_id' => CountryModel::factory()->create()->id,
        ]);

        $destroy = ProvinceFacade::destroy($province->id);
        $this->assertTrue($destroy->ok);
        $this->assertSoftDeleted(config('location.tables.province'), ['id' => $province->id]);

        $restore = ProvinceFacade::restore($province->id);
        $this->assertTrue($restore->ok);

        $destroyAgain = ProvinceFacade::destroy($province->id);
        $this->assertTrue($destroyAgain->ok);

        $force = ProvinceFacade::forceDelete($province->id);
        $this->assertTrue($force->ok);
        $this->assertDatabaseMissing(config('location.tables.province'), ['id' => $province->id]);
    }
}
