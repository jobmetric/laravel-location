<?php

namespace JobMetric\Location\Tests;

use JobMetric\Location\Facades\LocationCity;
use JobMetric\Location\Facades\LocationCountry;
use JobMetric\Location\Facades\LocationProvince;
use JobMetric\Location\Http\Resources\LocationCityResource;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class LocationCityTest extends BaseTestCase
{
    public function testStore(): void
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
    }

    public function testUpdate(): void
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

    public function testDelete(): void
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

        // Delete the city again
        $deleteLocationCity = LocationCity::delete($locationCity['data']->id);

        $this->assertIsArray($deleteLocationCity);
        $this->assertFalse($deleteLocationCity['ok']);
        $this->assertIsArray($deleteLocationCity['errors']);
        $this->assertEquals(404, $deleteLocationCity['status']);
    }

    public function testGet(): void
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

        $getLocationCity->each(function ($city) {
            $this->assertInstanceOf(\JobMetric\Location\Models\LocationCity::class, $city);

            $this->assertIsInt($city->id);
            $this->assertIsString($city->name);
            $this->assertIsBool($city->status);
        });
    }

    public function testPaginate(): void
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

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginateCities);
        $this->assertIsInt($paginateCities->total());
        $this->assertIsInt($paginateCities->perPage());
        $this->assertIsInt($paginateCities->currentPage());
        $this->assertIsInt($paginateCities->lastPage());
        $this->assertIsArray($paginateCities->items());
    }
}
