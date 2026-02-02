<?php

namespace JobMetric\Location\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Location\Models\Country as CountryModel;
use JobMetric\Location\Services\Country as CountryService;
use Throwable;

class CountryServiceTest extends ServiceTestCase
{
    /**
     * @throws Throwable
     */
    public function test_store_creates_country(): void
    {
        $service = app(CountryService::class);

        $res = $service->store([
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

        $service = app(CountryService::class);

        $service->store(['name' => 'Iran']);
        $service->store(['name' => 'Iran']);
    }

    /**
     * @throws Throwable
     */
    public function test_update_changes_name(): void
    {
        $service = app(CountryService::class);

        $country = CountryModel::factory()->setName('OldName')->create();

        $res = $service->update($country->id, [
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
        $service = app(CountryService::class);

        $country = CountryModel::factory()->setStatus(true)->create();
        $this->assertTrue((bool) $country->status);

        $res = $service->toggleStatus($country->id);
        $this->assertTrue($res->ok);

        $country->refresh();
        $this->assertFalse((bool) $country->status);
    }

    /**
     * @throws Throwable
     */
    public function test_destroy_restore_and_force_delete_cycle(): void
    {
        $service = app(CountryService::class);

        $country = CountryModel::factory()->create();

        $destroy = $service->destroy($country->id);
        $this->assertTrue($destroy->ok);
        $this->assertSoftDeleted(config('location.tables.country'), ['id' => $country->id]);

        $restore = $service->restore($country->id);
        $this->assertTrue($restore->ok);
        $this->assertDatabaseHas(config('location.tables.country'), [
            'id'         => $country->id,
            'deleted_at' => null,
        ]);

        // forceDelete is typically intended for trashed records.
        $destroyAgain = $service->destroy($country->id);
        $this->assertTrue($destroyAgain->ok);

        $force = $service->forceDelete($country->id);
        $this->assertTrue($force->ok);
        $this->assertDatabaseMissing(config('location.tables.country'), ['id' => $country->id]);
    }
}

