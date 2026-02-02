<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\City;

use JobMetric\Location\Http\Requests\City\UpdateCityRequest;
use JobMetric\Location\Models\City;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\Province;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class UpdateCityRequestTest extends RequestTestCase
{
    public function test_empty_payload_passes(): void
    {
        $data = [];
        $request = $this->makeRequest(UpdateCityRequest::class, $data, [
            'city_id'     => 1,
            'province_id' => 1,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertFalse($validator->fails());
    }

    public function test_duplicate_name_in_same_province_fails(): void
    {
        $province = Province::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);
        $c1 = City::factory()->create(['province_id' => $province->id, 'name' => 'Tehran']);
        $c2 = City::factory()->create(['province_id' => $province->id, 'name' => 'Other']);

        $data = ['province_id' => $province->id, 'name' => 'Tehran'];
        $request = $this->makeRequest(UpdateCityRequest::class, $data, [
            'city_id'     => $c2->id,
            'province_id' => $province->id,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_same_name_for_same_record_passes(): void
    {
        $province = Province::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);
        $c1 = City::factory()->create(['province_id' => $province->id, 'name' => 'Tehran']);

        $data = ['province_id' => $province->id, 'name' => 'Tehran'];
        $request = $this->makeRequest(UpdateCityRequest::class, $data, [
            'city_id'     => $c1->id,
            'province_id' => $province->id,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertFalse($validator->fails());
    }
}
