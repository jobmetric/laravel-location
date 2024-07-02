<?php

namespace JobMetric\Location\Tests;

use JobMetric\Location\Facades\LocationCountry;
use JobMetric\Location\Facades\LocationProvince;
use JobMetric\Location\Http\Resources\LocationCountryResource;
use JobMetric\Location\Http\Resources\LocationProvinceResource;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class LocationProvinceTest extends BaseTestCase
{
    public function test_store(): void
    {
        // Store a country by filling only the name field
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Store a province by filling only the name field
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        $this->assertIsArray($locationProvince);
        $this->assertTrue($locationProvince['ok']);
        $this->assertEquals(201, $locationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $locationProvince['data']);
        $this->assertIsInt($locationProvince['data']->id);
        $this->assertDatabaseHas(config('location.tables.province'), [
            'id' => $locationProvince['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'name' => 'Tehran',
            'status' => true,
        ]);

        // Store a province duplicate
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        $this->assertIsArray($locationProvince);
        $this->assertFalse($locationProvince['ok']);
        $this->assertIsArray($locationProvince['errors']);
        $this->assertEquals(422, $locationProvince['status']);

        // Store another province by filling all fields
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Khorasan Razavi',
            'status' => false,
        ]);

        $this->assertIsArray($locationProvince);
        $this->assertTrue($locationProvince['ok']);
        $this->assertEquals(201, $locationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $locationProvince['data']);
        $this->assertDatabaseHas(config('location.tables.province'), [
            'id' => $locationProvince['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'name' => 'Khorasan Razavi',
            'status' => false,
        ]);

        // store another country
        $anotherLocationCountry = LocationCountry::store([
            'name' => 'Iraq',
        ]);

        // Store a province by filling only the name field
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $anotherLocationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        $this->assertIsArray($locationProvince);
        $this->assertTrue($locationProvince['ok']);
        $this->assertEquals(201, $locationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $locationProvince['data']);
        $this->assertIsInt($locationProvince['data']->id);
        $this->assertDatabaseHas(config('location.tables.province'), [
            'id' => $locationProvince['data']->id,
            'location_country_id' => $anotherLocationCountry['data']->id,
            'name' => 'Tehran',
            'status' => true,
        ]);

        // Store a duplicate province in Iraq
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $anotherLocationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        $this->assertIsArray($locationProvince);
        $this->assertFalse($locationProvince['ok']);
        $this->assertIsArray($locationProvince['errors']);
        $this->assertEquals(422, $locationProvince['status']);
    }

    public function test_update(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Store a province by filling only the name field
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        // Update the province by filling only the name field
        $updateLocationProvince = LocationProvince::update($locationProvince['data']->id, [
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Mazandaran',
        ]);

        $this->assertIsArray($updateLocationProvince);
        $this->assertTrue($updateLocationProvince['ok']);
        $this->assertEquals(200, $updateLocationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $updateLocationProvince['data']);
        $this->assertDatabaseHas(config('location.tables.province'), [
            'id' => $updateLocationProvince['data']->id,
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Mazandaran',
            'status' => true,
        ]);

        // Store another province
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Khorasan Razavi',
        ]);

        // Update the province with a duplicate name
        $updateLocationProvince = LocationProvince::update($locationProvince['data']->id, [
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Mazandaran',
        ]);

        $this->assertIsArray($updateLocationProvince);
        $this->assertFalse($updateLocationProvince['ok']);
        $this->assertIsArray($updateLocationProvince['errors']);
        $this->assertEquals(422, $updateLocationProvince['status']);

        // Update the province with all fields
        $updateLocationProvince = LocationProvince::update($locationProvince['data']->id, [
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Khorasan Razavi',
            'status' => false,
        ]);

        $this->assertIsArray($updateLocationProvince);
        $this->assertTrue($updateLocationProvince['ok']);
        $this->assertEquals(200, $updateLocationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $updateLocationProvince['data']);
        $this->assertDatabaseHas(config('location.tables.province'), [
            'id' => $updateLocationProvince['data']->id,
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Khorasan Razavi',
            'status' => false,
        ]);
    }

    public function test_delete(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Store a province
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        // Delete the province
        $deleteLocationProvince = LocationProvince::delete($locationProvince['data']->id);

        $this->assertIsArray($deleteLocationProvince);
        $this->assertTrue($deleteLocationProvince['ok']);
        $this->assertEquals(200, $deleteLocationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $deleteLocationProvince['data']);

        $this->assertSoftDeleted(config('location.tables.province'), [
            'name' => 'Tehran',
        ]);

        // Delete the province again
        $deleteLocationProvince = LocationProvince::delete($locationProvince['data']->id);

        $this->assertIsArray($deleteLocationProvince);
        $this->assertFalse($deleteLocationProvince['ok']);
        $this->assertIsArray($deleteLocationProvince['errors']);
        $this->assertEquals(404, $deleteLocationProvince['status']);
    }

    public function test_restore(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Store a province
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        // Delete the province
        LocationProvince::delete($locationProvince['data']->id);

        // Restore the province
        $restoreLocationProvince = LocationProvince::restore($locationProvince['data']->id);

        $this->assertIsArray($restoreLocationProvince);
        $this->assertTrue($restoreLocationProvince['ok']);
        $this->assertEquals(200, $restoreLocationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $restoreLocationProvince['data']);

        $this->assertNotSoftDeleted(config('location.tables.province'), [
            'name' => 'Tehran',
        ]);

        // Restore the province again
        $restoreLocationProvince = LocationProvince::restore($locationProvince['data']->id);

        $this->assertIsArray($restoreLocationProvince);
        $this->assertFalse($restoreLocationProvince['ok']);
        $this->assertIsArray($restoreLocationProvince['errors']);
        $this->assertEquals(404, $restoreLocationProvince['status']);
    }

    public function test_force_delete(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Store a province
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        LocationProvince::delete($locationProvince['data']->id);

        // Force delete the province
        $forceDeleteLocationProvince = LocationProvince::forceDelete($locationProvince['data']->id);

        $this->assertIsArray($forceDeleteLocationProvince);
        $this->assertTrue($forceDeleteLocationProvince['ok']);
        $this->assertEquals(200, $forceDeleteLocationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $forceDeleteLocationProvince['data']);

        $this->assertDatabaseMissing(config('location.tables.province'), [
            'name' => 'Tehran',
        ]);

        // Force delete the province again
        $forceDeleteLocationProvince = LocationProvince::forceDelete($locationProvince['data']->id);

        $this->assertIsArray($forceDeleteLocationProvince);
        $this->assertFalse($forceDeleteLocationProvince['ok']);
        $this->assertIsArray($forceDeleteLocationProvince['errors']);
        $this->assertEquals(404, $forceDeleteLocationProvince['status']);
    }

    public function test_get(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Store a province
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        // Get the province
        $getLocationProvince = LocationProvince::get($locationProvince['data']->id);

        $this->assertIsArray($getLocationProvince);
        $this->assertTrue($getLocationProvince['ok']);
        $this->assertEquals(200, $getLocationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $getLocationProvince['data']);
        $this->assertEquals($locationProvince['data']->id, $getLocationProvince['data']->id);
        $this->assertEquals('Tehran', $getLocationProvince['data']->name);

        // Get the province with a wrong id
        $getLocationProvince = LocationProvince::get(1000);

        $this->assertIsArray($getLocationProvince);
        $this->assertFalse($getLocationProvince['ok']);
        $this->assertIsArray($getLocationProvince['errors']);
        $this->assertEquals(404, $getLocationProvince['status']);
    }

    public function test_all(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Store a province
        LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        // Get the province
        $getLocationProvinces = LocationProvince::all();

        $this->assertCount(1, $getLocationProvinces);

        $getLocationProvinces->each(function ($province) {
            $this->assertInstanceOf(LocationProvinceResource::class, $province);
        });
    }

    public function test_paginate(): void
    {
        // Store a country
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Store a province
        LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Tehran',
        ]);

        // Paginate the provinces
        $paginateProvinces = LocationProvince::paginate();

        $this->assertCount(1, $paginateProvinces);

        $paginateProvinces->each(function ($province) {
            $this->assertInstanceOf(LocationProvinceResource::class, $province);
        });

        $this->assertIsInt($paginateProvinces->total());
        $this->assertIsInt($paginateProvinces->perPage());
        $this->assertIsInt($paginateProvinces->currentPage());
        $this->assertIsInt($paginateProvinces->lastPage());
        $this->assertIsArray($paginateProvinces->items());
    }
}
