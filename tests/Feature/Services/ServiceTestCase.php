<?php

namespace JobMetric\Location\Tests\Feature\Services;

use JobMetric\Location\Models\City;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\District;
use JobMetric\Location\Models\Province;
use JobMetric\Location\Tests\TestCase;

abstract class ServiceTestCase extends TestCase
{
    /**
     * Create a full location hierarchy (country -> province -> city -> district).
     *
     * @return array{country: Country, province: Province, city: City, district: District}
     */
    protected function makeLocationGraph(): array
    {
        $country = Country::factory()->create();
        $province = Province::factory()->create(['country_id' => $country->id]);
        $city = City::factory()->create(['province_id' => $province->id]);
        $district = District::factory()->create(['city_id' => $city->id]);

        return compact('country', 'province', 'city', 'district');
    }
}

