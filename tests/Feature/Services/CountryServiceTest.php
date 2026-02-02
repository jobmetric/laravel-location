<?php

namespace JobMetric\Location\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Location\Facades\Country as CountryFacade;
use JobMetric\Location\Models\Country as CountryModel;
use Throwable;

class CountryServiceTest extends ServiceTestCase
{
    /**
     * @throws Throwable
     */
    public function test_store_creates_country(): void
    {
        $res = CountryFacade::store([
            'name'   => 'Iran',
            'status' => true,
        ]);

        $this->assertTrue($res->ok);
        $this->assertDatabaseHas(config('location.tables.country'), [
            'name' => 'Iran',
        ]);
    }

    /**
     * dto() validation should reject duplicate names.
     *
     * @throws Throwable
     */
    public function test_store_duplicate_name_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        CountryFacade::store(['name' => 'Iran']);
        CountryFacade::store(['name' => 'Iran']);
    }

    /**
     * @throws Throwable
     */
    public function test_update_changes_name(): void
    {
        $country = CountryModel::factory()->setName('OldName')->create();

        $res = CountryFacade::update($country->id, [
            'name' => 'NewName',
        ]);

        $this->assertTrue($res->ok);
        $this->assertDatabaseHas(config('location.tables.country'), [
            'id'   => $country->id,
            'name' => 'NewName',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_toggle_status_inverts_boolean_status(): void
    {
        $country = CountryModel::factory()->setStatus(true)->create();
        $this->assertTrue((bool) $country->status);

        $res = CountryFacade::toggleStatus($country->id);
        $this->assertTrue($res->ok);

        $country->refresh();
        $this->assertFalse((bool) $country->status);
    }

    /**
     * @throws Throwable
     */
    public function test_destroy_restore_and_force_delete_cycle(): void
    {
        $country = CountryModel::factory()->create();

        $destroy = CountryFacade::destroy($country->id);
        $this->assertTrue($destroy->ok);
        $this->assertSoftDeleted(config('location.tables.country'), ['id' => $country->id]);

        $restore = CountryFacade::restore($country->id);
        $this->assertTrue($restore->ok);
        $this->assertDatabaseHas(config('location.tables.country'), [
            'id'         => $country->id,
            'deleted_at' => null,
        ]);

        // forceDelete is typically intended for trashed records.
        $destroyAgain = CountryFacade::destroy($country->id);
        $this->assertTrue($destroyAgain->ok);

        $force = CountryFacade::forceDelete($country->id);
        $this->assertTrue($force->ok);
        $this->assertDatabaseMissing(config('location.tables.country'), ['id' => $country->id]);
    }
}
