<?php

namespace JobMetric\Location\Tests;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JobMetric\Location\Facades\LocationAddress;
use JobMetric\Location\Http\Resources\AddressResource;

class LocationAddressTest extends BaseLocation
{
    public function test_store(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $address = LocationAddress::store(array_merge(
            ['owner_type' => get_class($user), 'owner_id' => $user->id],
            [
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
            ]
        ));

        $this->assertIsArray($address);
        $this->assertTrue($address['ok']);
        $this->assertEquals(201, $address['status']);
        $this->assertInstanceOf(AddressResource::class, $address['data']);
        $this->assertIsInt($address['data']->id);

        $this->assertDatabaseHas(config('location.tables.address'), [
            'id' => $address['data']->id,
            'addressable_id' => $user->id,
            'addressable_type' => get_class($user),
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

    public function test_update(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $address = LocationAddress::store(array_merge(
            ['owner_type' => get_class($user), 'owner_id' => $user->id],
            [
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
            ]
        ));

        $address = LocationAddress::update($address['data']->id, [
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

        $this->assertIsArray($address);
        $this->assertTrue($address['ok']);
        $this->assertEquals(200, $address['status']);
        $this->assertInstanceOf(AddressResource::class, $address['data']);
        $this->assertIsInt($address['data']->id);

        $this->assertDatabaseHas(config('location.tables.address'), [
            'id' => $address['data']->id,
            'addressable_id' => $user->id,
            'addressable_type' => get_class($user),
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
    }

    public function test_delete(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $address = LocationAddress::store(array_merge(
            ['owner_type' => get_class($user), 'owner_id' => $user->id],
            [
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
            ]
        ));

        $address = LocationAddress::delete($address['data']->id);

        $this->assertIsArray($address);
        $this->assertTrue($address['ok']);
        $this->assertEquals(200, $address['status']);
        $this->assertInstanceOf(AddressResource::class, $address['data']);

        $this->assertSoftDeleted(config('location.tables.address'), [
            'id' => $address['data']->id
        ]);

        // try again
        $address = LocationAddress::delete($address['data']->id);

        $this->assertIsArray($address);
        $this->assertFalse($address['ok']);
        $this->assertEquals(404, $address['status']);
    }

    public function test_restore(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $address = LocationAddress::store(array_merge(
            ['owner_type' => get_class($user), 'owner_id' => $user->id],
            [
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
            ]
        ));

        LocationAddress::delete($address['data']->id);

        $address = LocationAddress::restore($address['data']->id);

        $this->assertIsArray($address);
        $this->assertTrue($address['ok']);
        $this->assertEquals(200, $address['status']);
        $this->assertInstanceOf(AddressResource::class, $address['data']);

        $this->assertDatabaseHas(config('location.tables.address'), [
            'id' => $address['data']->id,
            'addressable_id' => $user->id,
            'addressable_type' => get_class($user),
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

        // try again
        $address = LocationAddress::restore($address['data']->id);

        $this->assertIsArray($address);
        $this->assertFalse($address['ok']);
        $this->assertEquals(404, $address['status']);
    }

    public function test_force_delete(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $address = LocationAddress::store(array_merge(
            ['owner_type' => get_class($user), 'owner_id' => $user->id],
            [
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
            ]
        ));

        LocationAddress::delete($address['data']->id);

        $address = LocationAddress::forceDelete($address['data']->id);

        $this->assertIsArray($address);
        $this->assertTrue($address['ok']);
        $this->assertEquals(200, $address['status']);
        $this->assertInstanceOf(AddressResource::class, $address['data']);

        $this->assertDatabaseMissing(config('location.tables.address'), [
            'id' => $address['data']->id
        ]);

        // try again
        $address = LocationAddress::forceDelete($address['data']->id);

        $this->assertIsArray($address);
        $this->assertFalse($address['ok']);
        $this->assertEquals(404, $address['status']);
    }

    public function test_get(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        $address = LocationAddress::store(array_merge(
            ['owner_type' => get_class($user), 'owner_id' => $user->id],
            [
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
            ]
        ));

        $address = LocationAddress::get($address['data']->id);

        $this->assertIsArray($address);
        $this->assertTrue($address['ok']);
        $this->assertEquals(200, $address['status']);
        $this->assertInstanceOf(AddressResource::class, $address['data']);
        $this->assertIsInt($address['data']->id);

        // get wrong id
        $address = LocationAddress::get(1000);

        $this->assertIsArray($address);
        $this->assertFalse($address['ok']);
        $this->assertEquals(404, $address['status']);
    }

    public function test_all(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        LocationAddress::store(array_merge(
            ['owner_type' => get_class($user), 'owner_id' => $user->id],
            [
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
            ]
        ));

        $addresses = LocationAddress::all();

        $this->assertInstanceOf(AnonymousResourceCollection::class, $addresses);
        $this->assertCount(1, $addresses);

        $addresses->each(function ($address) {
            $this->assertInstanceOf(AddressResource::class, $address);
        });
    }

    public function test_paginate(): void
    {
        $user = $this->addUser();
        $location = $this->createLocation();

        LocationAddress::store(array_merge(
            ['owner_type' => get_class($user), 'owner_id' => $user->id],
            [
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
            ]
        ));

        $addresses = LocationAddress::paginate();

        $this->assertInstanceOf(AnonymousResourceCollection::class, $addresses);
        $this->assertCount(1, $addresses);

        $addresses->each(function ($address) {
            $this->assertInstanceOf(AddressResource::class, $address);
        });
    }
}
