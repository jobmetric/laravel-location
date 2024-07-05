<?php

namespace JobMetric\Location\Tests;

use App\Models\User;
use JobMetric\Location\Facades\LocationCity;
use JobMetric\Location\Facades\LocationCountry;
use JobMetric\Location\Facades\LocationDistrict;
use JobMetric\Location\Facades\LocationProvince;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class BaseLocation extends BaseTestCase
{
    public function addUser(): User
    {
        return User::factory()->create();
    }

    public function createLocation(): array
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

    public function addLocationCountry(string $name = null, bool $status = true): array
    {
        return LocationCountry::store([
            'name' => $name ?? 'Iran',
            'status' => $status,
        ]);
    }

    public function addLocationProvinceByCountry(array $locationCountry, string $name = null, bool $status = true): array
    {
        return LocationProvince::store([
            'location_country_id' => $locationCountry['data']->id,
            'name' => $name ?? 'Khorasan Razavi',
            'status' => $status,
        ]);
    }

    public function addLocationCityByProvince(array $locationCountry, array $locationProvince, string $name = null, bool $status = true): array
    {
        return LocationCity::store([
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'name' => $name ?? 'Mashhad',
            'status' => $status,
        ]);
    }

    public function addLocationDistrictByCity(array $locationCountry, array $locationProvince, array $locationCity, string $name = null, bool $status = true): array
    {
        return LocationDistrict::store([
            'location_country_id' => $locationCountry['data']->id,
            'location_province_id' => $locationProvince['data']->id,
            'location_city_id' => $locationCity['data']->id,
            'name' => $name ?? 'District 1',
            'status' => $status,
        ]);
    }

    public function createAssetIran(): array
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

    public function createAssetTurkey(): array
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
