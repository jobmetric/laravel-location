<?php

namespace JobMetric\Location\Tests;

use JobMetric\Location\Facades\LocationCountry;
use JobMetric\Location\Facades\LocationProvince;
use JobMetric\Location\Http\Resources\LocationCountryResource;
use JobMetric\Location\Http\Resources\LocationProvinceResource;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class LocationProvinceTest extends BaseTestCase
{
    public function testStore(): void
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

    public function testDeleteCountry(): void
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

        // Delete the country again
        $deleteLocationCountry = LocationCountry::delete($locationCountry['data']->id);

        $this->assertIsArray($deleteLocationCountry);
        $this->assertFalse($deleteLocationCountry['ok']);
        $this->assertIsArray($deleteLocationCountry['errors']);
        $this->assertEquals(404, $deleteLocationCountry['status']);
    }

    public function testGetCountry(): void
    {
        // Store a country
        LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Get the country
        $getCountries = LocationCountry::all();

        $getCountries->each(function ($country) {
            $this->assertInstanceOf(\JobMetric\Location\Models\LocationCountry::class, $country);

            $this->assertIsInt($country->id);
            $this->assertIsString($country->name);
            $this->assertNull($country->flag);
            $this->assertNull($country->mobile_prefix);
            $this->assertNull($country->validation);
            $this->assertIsBool($country->status);
        });
    }

    public function testPaginateCountry(): void
    {
        // Store a country
        LocationCountry::store([
            'name' => 'Iran',
        ]);

        // Paginate the country
        $paginateCountries = LocationCountry::paginate();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginateCountries);
        $this->assertIsInt($paginateCountries->total());
        $this->assertIsInt($paginateCountries->perPage());
        $this->assertIsInt($paginateCountries->currentPage());
        $this->assertIsInt($paginateCountries->lastPage());
        $this->assertIsArray($paginateCountries->items());
    }
}
