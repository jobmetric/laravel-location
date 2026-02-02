<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\City;

use JobMetric\Location\Http\Requests\City\StoreCityRequest;
use JobMetric\Location\Models\City;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\Province;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class StoreCityRequestTest extends RequestTestCase
{
    public function test_valid_payload_passes(): void
    {
        $province = Province::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);

        $data = [
            'province_id' => $province->id,
            'name'        => 'Tehran',
            'status'      => true,
        ];

        $request = $this->makeRequest(StoreCityRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
    }

    public function test_missing_province_id_fails(): void
    {
        $data = ['name' => 'Tehran'];
        $request = $this->makeRequest(StoreCityRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('province_id', $validator->errors()->toArray());
    }

    public function test_duplicate_name_in_same_province_fails(): void
    {
        $province = Province::factory()->create([
            'country_id' => Country::factory()->create()->id,
        ]);

        City::factory()->create(['province_id' => $province->id, 'name' => 'Tehran']);

        $data = ['province_id' => $province->id, 'name' => 'Tehran'];
        $request = $this->makeRequest(StoreCityRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
