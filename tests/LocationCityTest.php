<?php

namespace JobMetric\Location\Tests;

use JobMetric\Location\Facades\LocationCity;
use JobMetric\Location\Facades\LocationCountry;
use JobMetric\Location\Facades\LocationProvince;
use JobMetric\Location\Http\Resources\LocationCityResource;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class LocationCityTest extends BaseTestCase
{
    public function test_store(): void
    {
        // Store a country by filling only the name field
        $locationCountry = LocationCountry::store([
            'name' => 'Iran'
        ]);

        // Store a province by filling only the name field
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Khorasan Razavi'
        ]);

        // Store a city by filling only the name field
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Mashhad'
        ]);

        $this->assertIsArray($locationCity);
        $this->assertTrue($locationCity['ok']);
        $this->assertEquals(201, $locationCity['status']);
        $this->assertInstanceOf(LocationCityResource::class, $locationCity['data']);
        $this->assertIsInt($locationCity['data']->id);

        $this->assertDatabaseHas(config('location.tables.city'), [
            'id' => $locationCity['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'name' => 'Mashhad',
            'status' => true,
        ]);

        // Store a city duplicate
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Mashhad'
        ]);

        $this->assertIsArray($locationCity);
        $this->assertFalse($locationCity['ok']);
        $this->assertIsArray($locationCity['errors']);
        $this->assertEquals(422, $locationCity['status']);

        // Store another city by filling all fields
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Neishabour',
            'status' => false,
        ]);

        $this->assertIsArray($locationCity);
        $this->assertTrue($locationCity['ok']);
        $this->assertEquals(201, $locationCity['status']);
        $this->assertInstanceOf(LocationCityResource::class, $locationCity['data']);
        $this->assertIsInt($locationCity['data']->id);

        $this->assertDatabaseHas(config('location.tables.city'), [
            'id' => $locationCity['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'name' => 'Neishabour',
            'status' => false,
        ]);

        // store another province
        $locationProvince = LocationProvince::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            'name' => 'Mazandaran'
        ]);

        // Store a city by filling only the name field
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Mashhad'
        ]);

        $this->assertIsArray($locationCity);
        $this->assertTrue($locationCity['ok']);
        $this->assertEquals(201, $locationCity['status']);
        $this->assertInstanceOf(LocationCityResource::class, $locationCity['data']);
        $this->assertIsInt($locationCity['data']->id);
        $this->assertDatabaseHas(config('location.tables.city'), [
            'id' => $locationCity['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'name' => 'Mashhad',
            'status' => true,
        ]);

        // Store a duplicate city in Mazandaran
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Mashhad'
        ]);

        $this->assertIsArray($locationCity);
        $this->assertFalse($locationCity['ok']);
        $this->assertIsArray($locationCity['errors']);
        $this->assertEquals(422, $locationCity['status']);
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
            'name' => 'Khoraasan Razavi',
        ]);

        // Store a city by filling only the name field
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Mashhad',
        ]);

        // Update the city by filling only the name field
        $updateLocationCity = LocationCity::update($locationCity['data']->id, [
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Neishabour',
        ]);

        $this->assertIsArray($updateLocationCity);
        $this->assertTrue($updateLocationCity['ok']);
        $this->assertEquals(200, $updateLocationCity['status']);
        $this->assertInstanceOf(LocationCityResource::class, $updateLocationCity['data']);
        $this->assertDatabaseHas(config('location.tables.city'), [
            'id' => $updateLocationCity['data']->id,
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Neishabour',
            'status' => true,
        ]);

        // Store another city
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Mashhad',
        ]);

        // Update the city with a duplicate name
        $updateLocationCity = LocationCity::update($locationCity['data']->id, [
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Neishabour',
        ]);

        $this->assertIsArray($updateLocationCity);
        $this->assertFalse($updateLocationCity['ok']);
        $this->assertIsArray($updateLocationCity['errors']);
        $this->assertEquals(422, $updateLocationCity['status']);

        // Update the city with all fields
        $updateLocationCity = LocationCity::update($locationCity['data']->id, [
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Mashhad',
            'status' => false,
        ]);

        $this->assertIsArray($updateLocationCity);
        $this->assertTrue($updateLocationCity['ok']);
        $this->assertEquals(200, $updateLocationCity['status']);
        $this->assertInstanceOf(LocationCityResource::class, $updateLocationCity['data']);
        $this->assertDatabaseHas(config('location.tables.city'), [
            'id' => $updateLocationCity['data']->id,
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Mashhad',
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

        // Store a city
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Tehran',
        ]);

        // Delete the city
        $deleteLocationCity = LocationCity::delete($locationCity['data']->id);

        $this->assertIsArray($deleteLocationCity);
        $this->assertTrue($deleteLocationCity['ok']);
        $this->assertEquals(200, $deleteLocationCity['status']);
        $this->assertInstanceOf(LocationCityResource::class, $deleteLocationCity['data']);

        $this->assertSoftDeleted(config('location.tables.city'), [
            'name' => 'Tehran',
        ]);

        // Delete the city again
        $deleteLocationCity = LocationCity::delete($locationCity['data']->id);

        $this->assertIsArray($deleteLocationCity);
        $this->assertFalse($deleteLocationCity['ok']);
        $this->assertIsArray($deleteLocationCity['errors']);
        $this->assertEquals(404, $deleteLocationCity['status']);
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

        // Store a city
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Tehran',
        ]);

        // Delete the city
        LocationCity::delete($locationCity['data']->id);

        // Restore the city
        $restoreLocationCity = LocationCity::restore($locationCity['data']->id);

        $this->assertIsArray($restoreLocationCity);
        $this->assertTrue($restoreLocationCity['ok']);
        $this->assertEquals(200, $restoreLocationCity['status']);
        $this->assertInstanceOf(LocationCityResource::class, $restoreLocationCity['data']);

        $this->assertDatabaseHas(config('location.tables.city'), [
            'id' => $locationCity['data']->id,
            'name' => 'Tehran',
            'status' => true,
        ]);

        // Restore the city again
        $restoreLocationCity = LocationCity::restore($locationCity['data']->id);

        $this->assertIsArray($restoreLocationCity);
        $this->assertFalse($restoreLocationCity['ok']);
        $this->assertIsArray($restoreLocationCity['errors']);
        $this->assertEquals(404, $restoreLocationCity['status']);
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

        // Store a city
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Tehran',
        ]);

        // Delete the city
        LocationCity::delete($locationCity['data']->id);

        // Force delete the city
        $forceDeleteLocationCity = LocationCity::forceDelete($locationCity['data']->id);

        $this->assertIsArray($forceDeleteLocationCity);
        $this->assertTrue($forceDeleteLocationCity['ok']);
        $this->assertEquals(200, $forceDeleteLocationCity['status']);
        $this->assertInstanceOf(LocationCityResource::class, $forceDeleteLocationCity['data']);

        $this->assertDatabaseMissing(config('location.tables.city'), [
            'name' => 'Tehran',
        ]);

        // Force delete the city again
        $forceDeleteLocationCity = LocationCity::forceDelete($locationCity['data']->id);

        $this->assertIsArray($forceDeleteLocationCity);
        $this->assertFalse($forceDeleteLocationCity['ok']);
        $this->assertIsArray($forceDeleteLocationCity['errors']);
        $this->assertEquals(404, $forceDeleteLocationCity['status']);
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

        // Store a city
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Tehran',
        ]);

        // Get the city
        $getLocationCity = LocationCity::get($locationCity['data']->id);

        $this->assertIsArray($getLocationCity);
        $this->assertTrue($getLocationCity['ok']);
        $this->assertEquals(200, $getLocationCity['status']);
        $this->assertInstanceOf(LocationCityResource::class, $getLocationCity['data']);
        $this->assertIsInt($getLocationCity['data']->id);
        $this->assertEquals('Tehran', $getLocationCity['data']->name);
        $this->assertTrue($getLocationCity['data']->status);

        // Get the city with a wrong id
        $getLocationCity = LocationCity::get(1000);

        $this->assertIsArray($getLocationCity);
        $this->assertFalse($getLocationCity['ok']);
        $this->assertIsArray($getLocationCity['errors']);
        $this->assertEquals(404, $getLocationCity['status']);
    }

    public function test_all(): void
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

        // Store a city
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Tehran',
        ]);

        // Get the city
        $getLocationCity = LocationCity::all();

        $this->assertCount(1, $getLocationCity);

        $getLocationCity->each(function ($city) {
            $this->assertInstanceOf(LocationCityResource::class, $city);
        });
    }

    public function test_paginate(): void
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

        // Store a city
        LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Tehran',
        ]);

        // Paginate the cities
        $paginateCities = LocationCity::paginate();

        $this->assertCount(1, $paginateCities);

        $paginateCities->each(function ($city) {
            $this->assertInstanceOf(LocationCityResource::class, $city);
        });

        $this->assertIsInt($paginateCities->total());
        $this->assertIsInt($paginateCities->perPage());
        $this->assertIsInt($paginateCities->currentPage());
        $this->assertIsInt($paginateCities->lastPage());
        $this->assertIsArray($paginateCities->items());
    }
}
