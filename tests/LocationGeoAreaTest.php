<?php

namespace JobMetric\Location\Tests;

use JobMetric\Location\Facades\LocationCity;
use JobMetric\Location\Facades\LocationCountry;
use JobMetric\Location\Facades\LocationDistrict;
use JobMetric\Location\Facades\LocationGeoArea;
use JobMetric\Location\Facades\LocationProvince;
use JobMetric\Location\Http\Resources\LocationGeoAreaResource;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class LocationGeoAreaTest extends BaseTestCase
{
    public function test_store(): void
    {
        $assetIran = $this->createAssetIran();

        // Store a geo area by filling only the title field
        $locationGeoArea = LocationGeoArea::store([
            'title' => 'Geo Area 1',
        ]);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(201, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        $this->assertDatabaseHas(config('location.tables.geo_area'), [
            'id' => $locationGeoArea['data']->id,
            'title' => 'Geo Area 1',
            'status' => true,
        ]);

        // Store a geo area duplicate
        $locationGeoArea = LocationGeoArea::store([
            'title' => 'Geo Area 1',
        ]);

        $this->assertIsArray($locationGeoArea);
        $this->assertFalse($locationGeoArea['ok']);
        $this->assertIsArray($locationGeoArea['errors']);
        $this->assertEquals(422, $locationGeoArea['status']);

        // Store another geo area by filling all fields
        $locationGeoArea = LocationGeoArea::store([
            'title' => 'Geo Area 2',
            'description' => 'Description of Geo Area 2',
            'status' => false,
        ]);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(201, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        $this->assertDatabaseHas(config('location.tables.geo_area'), [
            'id' => $locationGeoArea['data']->id,
            'title' => 'Geo Area 2',
            'description' => 'Description of Geo Area 2',
            'status' => false,
        ]);

        // store another geo area by filling all fields
        $locationGeoArea = LocationGeoArea::store([
            'title' => 'Geo Area 3',
            'description' => 'Description of Geo Area 3',
            'status' => true,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetIran['locationCountry']['data']->id,
                    'location_province_id' => $assetIran['locationProvince']['data']->id,
                    'location_city_id' => $assetIran['locationCity']['data']->id,
                    'location_district_id' => $assetIran['locationDistrict']['data']->id,
                ]
            ],
        ]);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(201, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        $this->assertDatabaseHas(config('location.tables.geo_area'), [
            'id' => $locationGeoArea['data']->id,
            'title' => 'Geo Area 3',
            'description' => 'Description of Geo Area 3',
            'status' => true,
        ]);

        $this->assertDatabaseHas(config('location.tables.geo_area_zone'), [
            'location_geo_area_id' => $locationGeoArea['data']->id,
            'location_country_id' => $assetIran['locationCountry']['data']->id,
            'location_province_id' => $assetIran['locationProvince']['data']->id,
            'location_city_id' => $assetIran['locationCity']['data']->id,
            'location_district_id' => $assetIran['locationDistrict']['data']->id,
        ]);
    }

    public function test_update(): void
    {
        $assetIran = $this->createAssetIran();
        $assetTurkey = $this->createAssetTurkey();

        $locationGeoArea = LocationGeoArea::store([
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetIran['locationCountry']['data']->id,
                    'location_province_id' => $assetIran['locationProvince']['data']->id,
                    'location_city_id' => $assetIran['locationCity']['data']->id,
                    'location_district_id' => $assetIran['locationDistrict']['data']->id,
                ]
            ]
        ]);

        // Update a geo area
        $locationGeoArea = LocationGeoArea::update($locationGeoArea['data']->id, [
            'title' => 'Geo Area 1 Updated',
            'description' => 'Description of Geo Area 1 Updated',
            'status' => false,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetTurkey['locationCountry']['data']->id,
                    'location_province_id' => $assetTurkey['locationProvince']['data']->id,
                    'location_city_id' => $assetTurkey['locationCity']['data']->id,
                    'location_district_id' => $assetTurkey['locationDistrict']['data']->id,
                ]
            ]
        ]);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(200, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        $this->assertDatabaseHas(config('location.tables.geo_area'), [
            'id' => $locationGeoArea['data']->id,
            'title' => 'Geo Area 1 Updated',
            'description' => 'Description of Geo Area 1 Updated',
            'status' => false,
        ]);

        $this->assertDatabaseHas(config('location.tables.geo_area_zone'), [
            'location_geo_area_id' => $locationGeoArea['data']->id,
            'location_country_id' => $assetTurkey['locationCountry']['data']->id,
            'location_province_id' => $assetTurkey['locationProvince']['data']->id,
            'location_city_id' => $assetTurkey['locationCity']['data']->id,
            'location_district_id' => $assetTurkey['locationDistrict']['data']->id,
        ]);

        // Update a geo area with empty geo area zones
        $locationGeoArea = LocationGeoArea::update($locationGeoArea['data']->id, [
            'title' => 'Geo Area 1 Updated',
            'description' => 'Description of Geo Area 1 Updated',
            'status' => false,
            'geo_area_zones' => []
        ]);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(200, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        $this->assertDatabaseHas(config('location.tables.geo_area'), [
            'id' => $locationGeoArea['data']->id,
            'title' => 'Geo Area 1 Updated',
            'description' => 'Description of Geo Area 1 Updated',
            'status' => false,
        ]);

        $this->assertDatabaseMissing(config('location.tables.geo_area_zone'), [
            'location_geo_area_id' => $locationGeoArea['data']->id,
        ]);

        // Update a geo area with two geo area zones
        $locationGeoArea = LocationGeoArea::update($locationGeoArea['data']->id, [
            'title' => 'Geo Area 1 Updated',
            'description' => 'Description of Geo Area 1 Updated',
            'status' => false,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetIran['locationCountry']['data']->id,
                    'location_province_id' => $assetIran['locationProvince']['data']->id,
                    'location_city_id' => $assetIran['locationCity']['data']->id,
                    'location_district_id' => $assetIran['locationDistrict']['data']->id,
                ],
                [
                    'location_country_id' => $assetTurkey['locationCountry']['data']->id,
                    'location_province_id' => $assetTurkey['locationProvince']['data']->id,
                    'location_city_id' => $assetTurkey['locationCity']['data']->id,
                    'location_district_id' => $assetTurkey['locationDistrict']['data']->id,
                ]
            ]
        ]);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(200, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        $this->assertDatabaseHas(config('location.tables.geo_area'), [
            'id' => $locationGeoArea['data']->id,
            'title' => 'Geo Area 1 Updated',
            'description' => 'Description of Geo Area 1 Updated',
            'status' => false,
        ]);

        $this->assertDatabaseHas(config('location.tables.geo_area_zone'), [
            'location_geo_area_id' => $locationGeoArea['data']->id,
            'location_country_id' => $assetIran['locationCountry']['data']->id,
            'location_province_id' => $assetIran['locationProvince']['data']->id,
            'location_city_id' => $assetIran['locationCity']['data']->id,
            'location_district_id' => $assetIran['locationDistrict']['data']->id,
        ]);

        $this->assertDatabaseHas(config('location.tables.geo_area_zone'), [
            'location_geo_area_id' => $locationGeoArea['data']->id,
            'location_country_id' => $assetTurkey['locationCountry']['data']->id,
            'location_province_id' => $assetTurkey['locationProvince']['data']->id,
            'location_city_id' => $assetTurkey['locationCity']['data']->id,
            'location_district_id' => $assetTurkey['locationDistrict']['data']->id,
        ]);
    }

    public function test_delete(): void
    {
        $assetIran = $this->createAssetIran();

        $locationGeoArea = LocationGeoArea::store([
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetIran['locationCountry']['data']->id,
                    'location_province_id' => $assetIran['locationProvince']['data']->id,
                    'location_city_id' => $assetIran['locationCity']['data']->id,
                    'location_district_id' => $assetIran['locationDistrict']['data']->id,
                ]
            ]
        ]);

        // Delete a geo area
        $locationGeoArea = LocationGeoArea::delete($locationGeoArea['data']->id);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(200, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        $this->assertSoftDeleted(config('location.tables.geo_area'), [
            'id' => $locationGeoArea['data']->id,
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
        ]);

        $this->assertDatabaseHas(config('location.tables.geo_area_zone'), [
            'location_geo_area_id' => $locationGeoArea['data']->id,
            'location_country_id' => $assetIran['locationCountry']['data']->id,
            'location_province_id' => $assetIran['locationProvince']['data']->id,
            'location_city_id' => $assetIran['locationCity']['data']->id,
            'location_district_id' => $assetIran['locationDistrict']['data']->id,
        ]);

        // Delete the geo area again
        $locationGeoArea = LocationGeoArea::delete($locationGeoArea['data']->id);

        $this->assertIsArray($locationGeoArea);
        $this->assertFalse($locationGeoArea['ok']);
        $this->assertIsArray($locationGeoArea['errors']);
        $this->assertEquals(404, $locationGeoArea['status']);
    }

    public function test_restore(): void
    {
        $assetIran = $this->createAssetIran();

        $locationGeoArea = LocationGeoArea::store([
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetIran['locationCountry']['data']->id,
                    'location_province_id' => $assetIran['locationProvince']['data']->id,
                    'location_city_id' => $assetIran['locationCity']['data']->id,
                    'location_district_id' => $assetIran['locationDistrict']['data']->id,
                ]
            ]
        ]);

        $locationGeoArea = LocationGeoArea::delete($locationGeoArea['data']->id);

        // Restore a geo area
        $locationGeoArea = LocationGeoArea::restore($locationGeoArea['data']->id);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(200, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        $this->assertDatabaseHas(config('location.tables.geo_area'), [
            'id' => $locationGeoArea['data']->id,
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
        ]);

        $this->assertDatabaseHas(config('location.tables.geo_area_zone'), [
            'location_geo_area_id' => $locationGeoArea['data']->id,
            'location_country_id' => $assetIran['locationCountry']['data']->id,
            'location_province_id' => $assetIran['locationProvince']['data']->id,
            'location_city_id' => $assetIran['locationCity']['data']->id,
            'location_district_id' => $assetIran['locationDistrict']['data']->id,
        ]);

        // Restore the geo area again
        $locationGeoArea = LocationGeoArea::restore($locationGeoArea['data']->id);

        $this->assertIsArray($locationGeoArea);
        $this->assertFalse($locationGeoArea['ok']);
        $this->assertIsArray($locationGeoArea['errors']);
        $this->assertEquals(404, $locationGeoArea['status']);
    }

    public function test_force_delete(): void
    {
        $assetIran = $this->createAssetIran();

        $locationGeoArea = LocationGeoArea::store([
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetIran['locationCountry']['data']->id,
                    'location_province_id' => $assetIran['locationProvince']['data']->id,
                    'location_city_id' => $assetIran['locationCity']['data']->id,
                    'location_district_id' => $assetIran['locationDistrict']['data']->id,
                ]
            ]
        ]);

        $locationGeoArea = LocationGeoArea::delete($locationGeoArea['data']->id);

        // Force delete a geo area
        $locationGeoArea = LocationGeoArea::forceDelete($locationGeoArea['data']->id);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(200, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        $this->assertDatabaseMissing(config('location.tables.geo_area'), [
            'id' => $locationGeoArea['data']->id,
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
        ]);

        $this->assertDatabaseMissing(config('location.tables.geo_area_zone'), [
            'location_geo_area_id' => $locationGeoArea['data']->id,
            'location_country_id' => $assetIran['locationCountry']['data']->id,
            'location_province_id' => $assetIran['locationProvince']['data']->id,
            'location_city_id' => $assetIran['locationCity']['data']->id,
            'location_district_id' => $assetIran['locationDistrict']['data']->id,
        ]);

        // Force delete the geo area again
        $locationGeoArea = LocationGeoArea::forceDelete($locationGeoArea['data']->id);

        $this->assertIsArray($locationGeoArea);
        $this->assertFalse($locationGeoArea['ok']);
        $this->assertIsArray($locationGeoArea['errors']);
        $this->assertEquals(404, $locationGeoArea['status']);
    }

    public function test_get(): void
    {
        $assetIran = $this->createAssetIran();

        $locationGeoArea = LocationGeoArea::store([
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetIran['locationCountry']['data']->id,
                    'location_province_id' => $assetIran['locationProvince']['data']->id,
                    'location_city_id' => $assetIran['locationCity']['data']->id,
                    'location_district_id' => $assetIran['locationDistrict']['data']->id,
                ]
            ]
        ]);

        // Get a geo area
        $locationGeoArea = LocationGeoArea::get($locationGeoArea['data']->id);

        $this->assertIsArray($locationGeoArea);
        $this->assertTrue($locationGeoArea['ok']);
        $this->assertEquals(200, $locationGeoArea['status']);
        $this->assertInstanceOf(LocationGeoAreaResource::class, $locationGeoArea['data']);
        $this->assertIsInt($locationGeoArea['data']->id);

        // Get a geo area with wrong id
        $locationGeoArea = LocationGeoArea::get(1000);

        $this->assertIsArray($locationGeoArea);
        $this->assertFalse($locationGeoArea['ok']);
        $this->assertIsArray($locationGeoArea['errors']);
        $this->assertEquals(404, $locationGeoArea['status']);
    }

    public function test_all(): void
    {
        $assetIran = $this->createAssetIran();

        LocationGeoArea::store([
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetIran['locationCountry']['data']->id,
                    'location_province_id' => $assetIran['locationProvince']['data']->id,
                    'location_city_id' => $assetIran['locationCity']['data']->id,
                    'location_district_id' => $assetIran['locationDistrict']['data']->id,
                ]
            ]
        ]);

        // Get all geo areas
        $locationGeoAreas = LocationGeoArea::all();

        $this->assertCount(1, $locationGeoAreas);

        $locationGeoAreas->each(function ($geoArea) {
            $this->assertInstanceOf(LocationGeoAreaResource::class, $geoArea);
        });
    }

    public function test_paginate(): void
    {
        $assetIran = $this->createAssetIran();

        LocationGeoArea::store([
            'title' => 'Geo Area 1',
            'description' => 'Description of Geo Area 1',
            'status' => true,
            'geo_area_zones' => [
                [
                    'location_country_id' => $assetIran['locationCountry']['data']->id,
                    'location_province_id' => $assetIran['locationProvince']['data']->id,
                    'location_city_id' => $assetIran['locationCity']['data']->id,
                    'location_district_id' => $assetIran['locationDistrict']['data']->id,
                ]
            ]
        ]);

        // Get all geo areas
        $locationGeoAreas = LocationGeoArea::paginate();

        $this->assertCount(1, $locationGeoAreas);

        $locationGeoAreas->each(function ($geoArea) {
            $this->assertInstanceOf(LocationGeoAreaResource::class, $geoArea);
        });

        $this->assertIsInt($locationGeoAreas->total());
        $this->assertIsInt($locationGeoAreas->perPage());
        $this->assertIsInt($locationGeoAreas->currentPage());
        $this->assertIsInt($locationGeoAreas->lastPage());
        $this->assertIsArray($locationGeoAreas->items());
    }

    private function createAssetIran(): array
    {
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        $locationProvince = LocationProvince::store([
            'location_country_id' => $locationCountry['data']->id,
            'name' => 'Khorasan Razavi',
        ]);

        $locationCity = LocationCity::store([
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'name' => 'Mahshad',
        ]);

        $locationDistrict = LocationDistrict::store([
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'location_city_id' => $locationCity['data']->id,
            'name' => 'District 1',
        ]);

        return [
            'locationCountry' => $locationCountry,
            'locationProvince' => $locationProvince,
            'locationCity' => $locationCity,
            'locationDistrict' => $locationDistrict,
        ];
    }

    private function createAssetTurkey(): array
    {
        $locationCountry = LocationCountry::store([
            'name' => 'Turkey',
        ]);

        $locationProvince = LocationProvince::store([
            'location_country_id' => $locationCountry['data']->id,
            'name' => 'Istanbul',
        ]);

        $locationCity = LocationCity::store([
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'name' => 'Istanbul',
        ]);

        $locationDistrict = LocationDistrict::store([
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'location_city_id' => $locationCity['data']->id,
            'name' => 'District 1',
        ]);

        return [
            'locationCountry' => $locationCountry,
            'locationProvince' => $locationProvince,
            'locationCity' => $locationCity,
            'locationDistrict' => $locationDistrict,
        ];
    }
}
