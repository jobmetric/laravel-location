<?php

namespace JobMetric\Location\Tests\Feature\Services;

use Illuminate\Validation\ValidationException;
use JobMetric\Location\Facades\Address as AddressFacade;
use JobMetric\Location\Models\Address as AddressModel;
use JobMetric\Location\Models\LocationRelation;
use JobMetric\Location\Tests\Stubs\Models\TestUser;
use Throwable;

class AddressServiceTest extends ServiceTestCase
{
    /**
     * Address service requires owner_type/owner_id.
     *
     * @throws Throwable
     */
    public function test_store_without_owner_returns_422_response(): void
    {
        $res = AddressFacade::store([
            'country_id'  => 1,
            'province_id' => 1,
            'city_id'     => 1,
            'address'     => ['street' => 'X'],
        ]);

        $this->assertFalse($res->ok);
        $this->assertEquals(422, $res->status);
    }

    /**
     * dto() validation should reject invalid address keys.
     *
     * @throws Throwable
     */
    public function test_store_with_invalid_address_keys_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $owner = TestUser::query()->create([
            'name' => 'Owner'
        ]);
        $graph = $this->makeLocationGraph();

        AddressFacade::store([
            'owner_type'  => TestUser::class,
            'owner_id'    => $owner->id,
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'district_id' => $graph['district']->id,
            'address'     => [
                'street'      => 'jordan',
                'INVALID_KEY' => 'boom',
            ],
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_store_creates_address_and_location_relation(): void
    {
        $owner = TestUser::query()->create([
            'name' => 'Owner'
        ]);
        $graph = $this->makeLocationGraph();

        $res = AddressFacade::store([
            'owner_type'  => TestUser::class,
            'owner_id'    => $owner->id,
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'district_id' => $graph['district']->id,
            'address'     => [
                'street' => 'jordan',
                'number' => '10',
            ],
            'postcode'    => '1234567890',
            'info'        => [
                'name'          => 'Majid',
                'mobile_prefix' => '+98',
                'mobile'        => '9120000000',
            ],
        ]);

        $this->assertTrue($res->ok);
        $this->assertEquals(201, $res->status);

        /** @var AddressModel $address */
        $address = AddressModel::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas(config('location.tables.address'), [
            'id'         => $address->id,
            'owner_type' => TestUser::class,
            'owner_id'   => $owner->id,
            'postcode'   => '1234567890',
        ]);

        $this->assertDatabaseHas(config('location.tables.location_relation'), [
            'locationable_type' => AddressModel::class,
            'locationable_id'   => $address->id,
        ]);
    }

    /**
     * Update should version: soft-delete old row and create a new row with parent_id.
     *
     * @throws Throwable
     */
    public function test_update_versions_address_when_fields_change_and_preserves_location_relation(): void
    {
        $owner = TestUser::query()->create(['name' => 'Owner']);
        $graph = $this->makeLocationGraph();

        AddressFacade::store([
            'owner_type'  => TestUser::class,
            'owner_id'    => $owner->id,
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'district_id' => $graph['district']->id,
            'address'     => ['street' => 'jordan'],
            'postcode'    => '111',
        ]);

        $old = AddressModel::query()->latest('id')->firstOrFail();
        $oldLocationRelation = LocationRelation::query()
            ->where('locationable_type', AddressModel::class)
            ->where('locationable_id', $old->id)
            ->firstOrFail();

        $res = AddressFacade::update($old->id, [
            'postcode' => '222',
        ]);

        $this->assertTrue($res->ok);

        $old->refresh();
        $this->assertNotNull($old->deleted_at);

        $new = AddressModel::query()->where('parent_id', $old->id)->latest('id')->firstOrFail();
        $this->assertEquals('222', $new->postcode);

        $newLocationRelation = LocationRelation::query()
            ->where('locationable_type', AddressModel::class)
            ->where('locationable_id', $new->id)
            ->firstOrFail();

        $this->assertEquals($oldLocationRelation->location_id, $newLocationRelation->location_id);
    }

    /**
     * @throws Throwable
     */
    public function test_destroy_soft_deletes_address(): void
    {
        $owner = TestUser::query()->create([
            'name' => 'Owner'
        ]);
        $graph = $this->makeLocationGraph();

        AddressFacade::store([
            'owner_type'  => TestUser::class,
            'owner_id'    => $owner->id,
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'district_id' => $graph['district']->id,
            'address'     => ['street' => 'jordan'],
            'postcode'    => '111',
        ]);

        $address = AddressModel::query()->latest('id')->firstOrFail();

        $res = AddressFacade::destroy($address->id);
        $this->assertTrue($res->ok);

        $this->assertSoftDeleted(config('location.tables.address'), [
            'id' => $address->id,
        ]);
    }
}
