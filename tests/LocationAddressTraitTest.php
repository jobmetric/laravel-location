<?php

namespace JobMetric\Location\Tests;

use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JobMetric\Location\Http\Resources\AddressResource;
use Throwable;

class LocationAddressTraitTest extends BaseLocation
{
    public function test_location_address_trait_relationship()
    {
        $user = new User;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $user->addresses());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $user->addressLocationCountry());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $user->addressLocationProvince());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $user->addressLocationCity());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $user->addressLocationDistrict());
    }

    /**
     * @throws Throwable
     */
    public function test_store_address()
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $address = $user->storeAddress([
            'country_id' => $location['locationCountry']['data']->id,
            'province_id' => $location['locationProvince']['data']->id,
            'city_id' => $location['locationCity']['data']->id,
            'district_id' => $location['locationDistrict']['data']->id,
            'address' => 'address information',
            'pluck' => '20',
            'unit' => '10',
            'postcode' => '1234567890',
            'lat' => '1.234567890',
            'lng' => '1.234567890'
        ]);

        $this->assertDatabaseHas(config('location.tables.address'), [
            'id' => $address['data']->id,
            'addressable_id' => $user->id,
            'addressable_type' => User::class,
            'location_country_id' => $location['locationCountry']['data']->id,
            'location_province_id' => $location['locationProvince']['data']->id,
            'location_city_id' => $location['locationCity']['data']->id,
            'location_district_id' => $location['locationDistrict']['data']->id,
            'address' => 'address information',
            'pluck' => '20',
            'unit' => '10',
            'postcode' => '1234567890',
            'lat' => '1.234567890',
            'lng' => '1.234567890'
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_update_address(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $storeAddress = $user->storeAddress([
            'country_id' => $location['locationCountry']['data']->id,
            'province_id' => $location['locationProvince']['data']->id,
            'city_id' => $location['locationCity']['data']->id,
            'district_id' => $location['locationDistrict']['data']->id,
            'address' => 'address information',
            'pluck' => '20',
            'unit' => '10',
            'postcode' => '1234567890',
            'lat' => '1.234567890',
            'lng' => '1.234567890'
        ]);

        $updateAddress = $user->updateAddress($storeAddress['data']->id, [
            'country_id' => $location['locationCountry']['data']->id,
            'province_id' => $location['locationProvince']['data']->id,
            'city_id' => $location['locationCity']['data']->id,
            'district_id' => $location['locationDistrict']['data']->id,
            'address' => 'address information updated',
            'pluck' => '21',
            'unit' => '11',
            'postcode' => '1234567891',
            'lat' => '1.234567891',
            'lng' => '1.234567891'
        ]);

        $this->assertDatabaseHas(config('location.tables.address'), [
            'id' => $updateAddress['data']->id,
            'addressable_id' => $user->id,
            'addressable_type' => User::class,
            'location_country_id' => $location['locationCountry']['data']->id,
            'location_province_id' => $location['locationProvince']['data']->id,
            'location_city_id' => $location['locationCity']['data']->id,
            'location_district_id' => $location['locationDistrict']['data']->id,
            'address' => 'address information updated',
            'pluck' => '21',
            'unit' => '11',
            'postcode' => '1234567891',
            'lat' => '1.234567891',
            'lng' => '1.234567891'
        ]);

        // update another address
        $updateAddress = $user->updateAddress(1000, [
            'country_id' => $location['locationCountry']['data']->id,
            'province_id' => $location['locationProvince']['data']->id,
            'city_id' => $location['locationCity']['data']->id,
            'district_id' => $location['locationDistrict']['data']->id,
            'address' => 'address information updated',
            'pluck' => '21',
            'unit' => '11',
            'postcode' => '1234567891',
            'lat' => '1.234567891',
            'lng' => '1.234567891'
        ]);

        $this->assertIsArray($updateAddress);
        $this->assertArrayHasKey('ok', $updateAddress);
        $this->assertFalse($updateAddress['ok']);
        $this->assertEquals($updateAddress['message'], trans('location::base.validation.object_not_found', ['name' => trans('location::base.model_name.address')]));
        $this->assertArrayHasKey('error', $updateAddress);
        $this->assertEquals(422, $updateAddress['status']);
    }

    /**
     * @throws Throwable
     */
    public function test_get_address(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $user->storeAddress([
            'country_id' => $location['locationCountry']['data']->id,
            'province_id' => $location['locationProvince']['data']->id,
            'city_id' => $location['locationCity']['data']->id,
            'district_id' => $location['locationDistrict']['data']->id,
            'address' => 'address information',
            'pluck' => '20',
            'unit' => '10',
            'postcode' => '1234567890',
            'lat' => '1.234567890',
            'lng' => '1.234567890'
        ]);

        $addresses = $user->getAddress();

        $this->assertInstanceOf(AnonymousResourceCollection::class, $addresses);
        $this->assertCount(1, $addresses);

        $addresses->each(function ($address) {
            $this->assertInstanceOf(AddressResource::class, $address);
        });
    }

    /**
     * @throws Throwable
     */
    public function test_forget(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $storeAddress = $user->storeAddress([
            'country_id' => $location['locationCountry']['data']->id,
            'province_id' => $location['locationProvince']['data']->id,
            'city_id' => $location['locationCity']['data']->id,
            'district_id' => $location['locationDistrict']['data']->id,
            'address' => 'address information',
            'pluck' => '20',
            'unit' => '10',
            'postcode' => '1234567890',
            'lat' => '1.234567890',
            'lng' => '1.234567890'
        ]);

        $delete = $user->forgetAddress($storeAddress['data']->id);

        $this->assertTrue($delete);

        $this->assertSoftDeleted(config('location.tables.address'), [
            'id' => $storeAddress['data']->id,
            'addressable_id' => $user->id,
            'addressable_type' => User::class,
            'location_country_id' => $location['locationCountry']['data']->id,
            'location_province_id' => $location['locationProvince']['data']->id,
            'location_city_id' => $location['locationCity']['data']->id,
            'location_district_id' => $location['locationDistrict']['data']->id,
            'address' => 'address information',
            'pluck' => '20',
            'unit' => '10',
            'postcode' => '1234567890',
            'lat' => '1.234567890',
            'lng' => '1.234567890'
        ]);

        // try again this address
        $delete = $user->forgetAddress($storeAddress['data']->id);

        $this->assertFalse($delete);

        // try another address
        $delete = $user->forgetAddress(1000);

        $this->assertFalse($delete);
    }
}
