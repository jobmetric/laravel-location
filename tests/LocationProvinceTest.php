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

        // Delete the province
        $deleteLocationProvince = LocationProvince::delete($locationProvince['data']->id);

        $this->assertIsArray($deleteLocationProvince);
        $this->assertTrue($deleteLocationProvince['ok']);
        $this->assertEquals(200, $deleteLocationProvince['status']);
        $this->assertInstanceOf(LocationProvinceResource::class, $deleteLocationProvince['data']);

        // Delete the province again
        $deleteLocationProvince = LocationProvince::delete($locationProvince['data']->id);

        $this->assertIsArray($deleteLocationProvince);
        $this->assertFalse($deleteLocationProvince['ok']);
        $this->assertIsArray($deleteLocationProvince['errors']);
        $this->assertEquals(404, $deleteLocationProvince['status']);
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

        // Get the province
        $getLocationProvinces = LocationProvince::all();

        $getLocationProvinces->each(function ($province) {
            $this->assertInstanceOf(\JobMetric\Location\Models\LocationProvince::class, $province);

            $this->assertIsInt($province->id);
            $this->assertIsString($province->name);
            $this->assertIsBool($province->status);
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

        // Paginate the provinces
        $paginateProvinces = LocationProvince::paginate();

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginateProvinces);
        $this->assertIsInt($paginateProvinces->total());
        $this->assertIsInt($paginateProvinces->perPage());
        $this->assertIsInt($paginateProvinces->currentPage());
        $this->assertIsInt($paginateProvinces->lastPage());
        $this->assertIsArray($paginateProvinces->items());
    }
}
