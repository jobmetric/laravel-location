<?php

namespace JobMetric\Location\Tests;

use JobMetric\Location\Facades\LocationCountry;
use JobMetric\Location\Http\Resources\LocationCountryResource;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class LocationCountryTest extends BaseTestCase
{
    public function test_store(): void
    {
        // Store a country by filling only the name field
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        $this->assertIsArray($locationCountry);
        $this->assertTrue($locationCountry['ok']);
        $this->assertEquals(201, $locationCountry['status']);
        $this->assertInstanceOf(LocationCountryResource::class, $locationCountry['data']);
        $this->assertIsInt($locationCountry['data']->id);
        $this->assertEquals('Iran', $locationCountry['data']->name);
        $this->assertNull($locationCountry['data']->flag);
        $this->assertNull($locationCountry['data']->mobile_prefix);
        $this->assertNull($locationCountry['data']->validation);
        $this->assertTrue($locationCountry['data']->status);

        // Store a country duplicate
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        $this->assertIsArray($locationCountry);
        $this->assertFalse($locationCountry['ok']);
        $this->assertIsArray($locationCountry['errors']);
        $this->assertEquals(422, $locationCountry['status']);

        // Store another country by filling all fields
        $locationCountry = LocationCountry::store([
            'name' => 'Turkey',
            'flag' => 'tr',
            'mobile_prefix' => 90,
            'validation' => [
                'phone' => '09',
                'mobile' => '05',
            ],
            'status' => false,
        ]);

        $this->assertIsArray($locationCountry);
        $this->assertTrue($locationCountry['ok']);
        $this->assertEquals(201, $locationCountry['status']);
        $this->assertIsInt($locationCountry['data']->id);
        $this->assertEquals('Turkey', $locationCountry['data']->name);
        $this->assertEquals('tr', $locationCountry['data']->flag);
        $this->assertEquals(90, $locationCountry['data']->mobile_prefix);
        $this->assertIsArray($locationCountry['data']->validation);
        $this->assertFalse($locationCountry['data']->status);
    }

    public function test_update(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Update the country
        $updateLocationCountry = LocationCountry::update($locationCountry['data']->id, [
            'name' => 'Iran',
        ]);

        $this->assertIsArray($updateLocationCountry);
        $this->assertTrue($updateLocationCountry['ok']);
        $this->assertEquals(200, $updateLocationCountry['status']);
        $this->assertInstanceOf(LocationCountryResource::class, $updateLocationCountry['data']);
        $this->assertEquals($locationCountry['data']->id, $updateLocationCountry['data']->id);
        $this->assertEquals('Iran', $updateLocationCountry['data']->name);

        // Store another country
        $storeLocationCountry = LocationCountry::store([
            'name' => 'Turkey'
        ]);

        // Update the country with a duplicate name
        $updateLocationCountry = LocationCountry::update($storeLocationCountry['data']->id, [
            'name' => 'Iran'
        ]);

        $this->assertIsArray($updateLocationCountry);
        $this->assertFalse($updateLocationCountry['ok']);
        $this->assertIsArray($updateLocationCountry['errors']);
        $this->assertEquals(422, $updateLocationCountry['status']);

        // Update the country with all fields
        $updateLocationCountry = LocationCountry::update($storeLocationCountry['data']->id, [
            'name' => 'Iraq',
            'flag' => 'iq',
            'mobile_prefix' => 964,
            'validation' => [
                'phone' => '01',
                'mobile' => '07',
            ],
            'status' => false,
        ]);

        $this->assertIsArray($updateLocationCountry);
        $this->assertTrue($updateLocationCountry['ok']);
        $this->assertEquals(200, $updateLocationCountry['status']);
        $this->assertEquals($storeLocationCountry['data']->id, $updateLocationCountry['data']->id);
        $this->assertEquals('Iraq', $updateLocationCountry['data']->name);
        $this->assertEquals('iq', $updateLocationCountry['data']->flag);
        $this->assertEquals(964, $updateLocationCountry['data']->mobile_prefix);
        $this->assertIsArray($updateLocationCountry['data']->validation);
        $this->assertFalse($updateLocationCountry['data']->status);
    }

    public function test_delete(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Delete the country
        $deleteLocationCountry = LocationCountry::delete($locationCountry['data']->id);

        $this->assertIsArray($deleteLocationCountry);
        $this->assertTrue($deleteLocationCountry['ok']);
        $this->assertEquals(200, $deleteLocationCountry['status']);
        $this->assertInstanceOf(LocationCountryResource::class, $deleteLocationCountry['data']);

        $this->assertSoftDeleted('location_countries', [
            'name' => 'Iran',
        ]);

        // Delete the country again
        $deleteLocationCountry = LocationCountry::delete($locationCountry['data']->id);

        $this->assertIsArray($deleteLocationCountry);
        $this->assertFalse($deleteLocationCountry['ok']);
        $this->assertIsArray($deleteLocationCountry['errors']);
        $this->assertEquals(404, $deleteLocationCountry['status']);
    }

    public function test_restore(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Delete the country
        $deleteLocationCountry = LocationCountry::delete($locationCountry['data']->id);

        // Restore the country
        $restoreLocationCountry = LocationCountry::restore($locationCountry['data']->id);

        $this->assertIsArray($restoreLocationCountry);
        $this->assertTrue($restoreLocationCountry['ok']);
        $this->assertEquals(200, $restoreLocationCountry['status']);
        $this->assertInstanceOf(LocationCountryResource::class, $restoreLocationCountry['data']);

        $this->assertNotSoftDeleted('location_countries', [
            'name' => 'Iran',
        ]);

        // Restore the country again
        $restoreLocationCountry = LocationCountry::restore($locationCountry['data']->id);

        $this->assertIsArray($restoreLocationCountry);
        $this->assertFalse($restoreLocationCountry['ok']);
        $this->assertIsArray($restoreLocationCountry['errors']);
        $this->assertEquals(404, $restoreLocationCountry['status']);
    }

    public function test_force_delete(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Delete the country
        LocationCountry::delete($locationCountry['data']->id);

        // Force delete the country
        $forceDeleteLocationCountry = LocationCountry::forceDelete($locationCountry['data']->id);

        $this->assertIsArray($forceDeleteLocationCountry);
        $this->assertTrue($forceDeleteLocationCountry['ok']);
        $this->assertEquals(200, $forceDeleteLocationCountry['status']);
        $this->assertInstanceOf(LocationCountryResource::class, $forceDeleteLocationCountry['data']);

        $this->assertDatabaseMissing('location_countries', [
            'name' => 'Iran',
        ]);

        // Force delete the country again
        $forceDeleteLocationCountry = LocationCountry::forceDelete($locationCountry['data']->id);

        $this->assertIsArray($forceDeleteLocationCountry);
        $this->assertFalse($forceDeleteLocationCountry['ok']);
        $this->assertIsArray($forceDeleteLocationCountry['errors']);
        $this->assertEquals(404, $forceDeleteLocationCountry['status']);
    }

    public function test_get(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Get the country
        $getLocationCountry = LocationCountry::get($locationCountry['data']->id);

        $this->assertIsArray($getLocationCountry);
        $this->assertTrue($getLocationCountry['ok']);
        $this->assertEquals(200, $getLocationCountry['status']);
        $this->assertInstanceOf(LocationCountryResource::class, $getLocationCountry['data']);
        $this->assertEquals($locationCountry['data']->id, $getLocationCountry['data']->id);
        $this->assertEquals('Iran', $getLocationCountry['data']->name);

        // Get the country with a wrong id
        $getLocationCountry = LocationCountry::get(1000);

        $this->assertIsArray($getLocationCountry);
        $this->assertFalse($getLocationCountry['ok']);
        $this->assertIsArray($getLocationCountry['errors']);
        $this->assertEquals(404, $getLocationCountry['status']);
    }

    public function test_all(): void
    {
        // Store a country
        LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Get the country
        $getCountries = LocationCountry::all();

        $this->assertCount(1, $getCountries);

        $getCountries->each(function ($country) {
            $this->assertInstanceOf(LocationCountryResource::class, $country);
        });
    }

    public function test_paginate(): void
    {
        // Store a country
        LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Paginate the country
        $paginateCountries = LocationCountry::paginate();

        $this->assertCount(1, $paginateCountries);

        $paginateCountries->each(function ($country) {
            $this->assertInstanceOf(LocationCountryResource::class, $country);
        });

        $this->assertIsInt($paginateCountries->total());
        $this->assertIsInt($paginateCountries->perPage());
        $this->assertIsInt($paginateCountries->currentPage());
        $this->assertIsInt($paginateCountries->lastPage());
        $this->assertIsArray($paginateCountries->items());
    }
}
