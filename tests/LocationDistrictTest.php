<?php

namespace JobMetric\Location\Tests;

use JobMetric\Location\Facades\LocationCity;
use JobMetric\Location\Facades\LocationCountry;
use JobMetric\Location\Facades\LocationDistrict;
use JobMetric\Location\Facades\LocationProvince;
use JobMetric\Location\Http\Resources\LocationCityResource;
use JobMetric\Location\Http\Resources\LocationDistrictResource;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class LocationDistrictTest extends BaseTestCase
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

        // Store a district by filling only the name field
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 1'
        ]);

        $this->assertIsArray($locationDistrict);
        $this->assertTrue($locationDistrict['ok']);
        $this->assertEquals(201, $locationDistrict['status']);
        $this->assertInstanceOf(LocationDistrictResource::class, $locationDistrict['data']);
        $this->assertIsInt($locationDistrict['data']->id);

        $this->assertDatabaseHas(config('location.tables.district'), [
            'id' => $locationCity['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'location_city_id' => $locationCity['data']->id,
            'name' => 'District 1',
            'status' => true,
        ]);

        // Store a district duplicate
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 1'
        ]);

        $this->assertIsArray($locationDistrict);
        $this->assertFalse($locationDistrict['ok']);
        $this->assertIsArray($locationDistrict['errors']);
        $this->assertEquals(422, $locationDistrict['status']);

        // Store another district by filling all fields
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 2',
            'status' => false
        ]);

        $this->assertIsArray($locationDistrict);
        $this->assertTrue($locationDistrict['ok']);
        $this->assertEquals(201, $locationDistrict['status']);
        $this->assertInstanceOf(LocationDistrictResource::class, $locationDistrict['data']);
        $this->assertIsInt($locationDistrict['data']->id);

        $this->assertDatabaseHas(config('location.tables.district'), [
            'id' => $locationDistrict['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'location_city_id' => $locationCity['data']->id,
            'name' => 'District 2',
            'status' => false,
        ]);

        // store another city
        $locationCity = LocationCity::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            'name' => 'Tehran'
        ]);

        // Store a district by filling only the name field
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 3'
        ]);

        $this->assertIsArray($locationDistrict);
        $this->assertTrue($locationDistrict['ok']);
        $this->assertEquals(201, $locationDistrict['status']);
        $this->assertInstanceOf(LocationDistrictResource::class, $locationDistrict['data']);
        $this->assertIsInt($locationDistrict['data']->id);

        $this->assertDatabaseHas(config('location.tables.district'), [
            'id' => $locationDistrict['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'location_city_id' => $locationCity['data']->id,
            'name' => 'District 3',
            'status' => true,
        ]);

        // Store a duplicate district in Tehran city
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 3'
        ]);

        $this->assertIsArray($locationDistrict);
        $this->assertFalse($locationDistrict['ok']);
        $this->assertIsArray($locationDistrict['errors']);
        $this->assertEquals(422, $locationDistrict['status']);
    }

    public function test_update(): void
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

        // Store a district by filling only the name field
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 1'
        ]);

        // Update the district by filling only the name field
        $locationDistrict = LocationDistrict::update($locationDistrict['data']->id, [
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 2'
        ]);

        $this->assertIsArray($locationDistrict);
        $this->assertTrue($locationDistrict['ok']);
        $this->assertEquals(200, $locationDistrict['status']);
        $this->assertInstanceOf(LocationDistrictResource::class, $locationDistrict['data']);
        $this->assertIsInt($locationDistrict['data']->id);

        $this->assertDatabaseHas(config('location.tables.district'), [
            'id' => $locationDistrict['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'location_city_id' => $locationCity['data']->id,
            'name' => 'District 2',
            'status' => true,
        ]);

        // Store another district
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 3'
        ]);

        // Update the district with a duplicate name
        $updateLocationDistrict = LocationDistrict::update($locationDistrict['data']->id, [
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 2'
        ]);

        $this->assertIsArray($updateLocationDistrict);
        $this->assertFalse($updateLocationDistrict['ok']);
        $this->assertIsArray($updateLocationDistrict['errors']);
        $this->assertEquals(422, $updateLocationDistrict['status']);

        // Update the district with all fields
        $locationDistrict = LocationDistrict::update($locationDistrict['data']->id, [
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 4',
            'status' => false
        ]);

        $this->assertIsArray($locationDistrict);
        $this->assertTrue($locationDistrict['ok']);
        $this->assertEquals(200, $locationDistrict['status']);
        $this->assertInstanceOf(LocationDistrictResource::class, $locationDistrict['data']);
        $this->assertIsInt($locationDistrict['data']->id);

        $this->assertDatabaseHas(config('location.tables.district'), [
            'id' => $locationDistrict['data']->id,
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'location_city_id' => $locationCity['data']->id,
            'name' => 'District 4',
            'status' => false,
        ]);
    }

    public function test_delete(): void
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

        // Store a district by filling only the name field
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 1'
        ]);

        // Delete the district
        $destroyLocationDistrict = LocationDistrict::delete($locationDistrict['data']->id);

        $this->assertIsArray($destroyLocationDistrict);
        $this->assertTrue($destroyLocationDistrict['ok']);
        $this->assertEquals(200, $destroyLocationDistrict['status']);
        $this->assertInstanceOf(LocationDistrictResource::class, $destroyLocationDistrict['data']);

        $this->assertSoftDeleted(config('location.tables.district'), [
            'name' => 'District 1',
        ]);

        // Delete the city again
        $destroyLocationDistrict = LocationDistrict::delete($locationDistrict['data']->id);

        $this->assertIsArray($destroyLocationDistrict);
        $this->assertFalse($destroyLocationDistrict['ok']);
        $this->assertIsArray($destroyLocationDistrict['errors']);
        $this->assertEquals(404, $destroyLocationDistrict['status']);
    }

    public function test_restore(): void
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

        // Store a district by filling only the name field
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 1'
        ]);

        // Delete the district
        LocationDistrict::delete($locationDistrict['data']->id);

        // Restore the district
        $restoreLocationDistrict = LocationDistrict::restore($locationDistrict['data']->id);

        $this->assertIsArray($restoreLocationDistrict);
        $this->assertTrue($restoreLocationDistrict['ok']);
        $this->assertEquals(200, $restoreLocationDistrict['status']);
        $this->assertInstanceOf(LocationDistrictResource::class, $restoreLocationDistrict['data']);

        $this->assertDatabaseHas(config('location.tables.district'), [
            'id' => $locationDistrict['data']->id,
            'name' => 'District 1',
            'status' => true,
        ]);

        // Restore the district again
        $restoreLocationDistrict = LocationDistrict::restore($locationDistrict['data']->id);

        $this->assertIsArray($restoreLocationDistrict);
        $this->assertFalse($restoreLocationDistrict['ok']);
        $this->assertIsArray($restoreLocationDistrict['errors']);
        $this->assertEquals(404, $restoreLocationDistrict['status']);
    }

    public function test_force_delete(): void
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

        // Store a district by filling only the name field
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 1'
        ]);

        // Delete the district
        LocationDistrict::delete($locationDistrict['data']->id);

        // Force delete the district
        $forceDeleteLocationDistrict = LocationDistrict::forceDelete($locationDistrict['data']->id);

        $this->assertIsArray($forceDeleteLocationDistrict);
        $this->assertTrue($forceDeleteLocationDistrict['ok']);
        $this->assertEquals(200, $forceDeleteLocationDistrict['status']);
        $this->assertInstanceOf(LocationDistrictResource::class, $forceDeleteLocationDistrict['data']);

        $this->assertDatabaseMissing(config('location.tables.district'), [
            'name' => 'District 1',
        ]);

        // Force delete the district again
        $forceDeleteLocationDistrict = LocationDistrict::forceDelete($locationDistrict['data']->id);

        $this->assertIsArray($forceDeleteLocationDistrict);
        $this->assertFalse($forceDeleteLocationDistrict['ok']);
        $this->assertIsArray($forceDeleteLocationDistrict['errors']);
        $this->assertEquals(404, $forceDeleteLocationDistrict['status']);
    }

    public function test_get(): void
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

        // Store a district by filling only the name field
        $locationDistrict = LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 1'
        ]);

        // Get the district
        $getLocationDistrict = LocationDistrict::get($locationDistrict['data']->id);

        $this->assertIsArray($getLocationDistrict);
        $this->assertTrue($getLocationDistrict['ok']);
        $this->assertEquals(200, $getLocationDistrict['status']);
        $this->assertInstanceOf(LocationDistrictResource::class, $getLocationDistrict['data']);
        $this->assertIsInt($getLocationDistrict['data']->id);

        // Get the district with a wrong id
        $getLocationDistrict = LocationDistrict::get(1000);

        $this->assertIsArray($getLocationDistrict);
        $this->assertFalse($getLocationDistrict['ok']);
        $this->assertIsArray($getLocationDistrict['errors']);
        $this->assertEquals(404, $getLocationDistrict['status']);
    }

    public function test_all(): void
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

        // Store a district by filling only the name field
        LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 1'
        ]);

        // Store another district by filling only the name field
        LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 2'
        ]);

        // Get all districts
        $allLocationDistricts = LocationDistrict::all();

        $this->assertCount(2, $allLocationDistricts);

        $allLocationDistricts->each(function ($district) {
            $this->assertInstanceOf(LocationDistrictResource::class, $district);
        });
    }

    public function test_paginate(): void
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

        // Store a district by filling only the name field
        LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 1'
        ]);

        // Store another district by filling only the name field
        LocationDistrict::store([
            config('location.foreign_key.country') => $locationCountry['data']->id,
            config('location.foreign_key.province') => $locationProvince['data']->id,
            config('location.foreign_key.city') => $locationCity['data']->id,
            'name' => 'District 2'
        ]);

        // Get all districts
        $paginateDistricts = LocationDistrict::paginate();

        $this->assertCount(2, $paginateDistricts);

        $paginateDistricts->each(function ($city) {
            $this->assertInstanceOf(LocationDistrictResource::class, $city);
        });

        $this->assertIsInt($paginateDistricts->total());
        $this->assertIsInt($paginateDistricts->perPage());
        $this->assertIsInt($paginateDistricts->currentPage());
        $this->assertIsInt($paginateDistricts->lastPage());
        $this->assertIsArray($paginateDistricts->items());
    }
}
