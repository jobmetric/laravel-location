<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\Province;

use JobMetric\Location\Http\Requests\Province\UpdateProvinceRequest;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\Province;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class UpdateProvinceRequestTest extends RequestTestCase
{
    public function test_empty_payload_passes(): void
    {
        $data = [];
        $request = $this->makeRequest(UpdateProvinceRequest::class, $data, [
            'province_id' => 1,
            'country_id'  => 1,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertFalse($validator->fails());
    }

    public function test_duplicate_name_in_same_country_fails(): void
    {
        $country = Country::factory()->create();
        $p1 = Province::factory()->create(['country_id' => $country->id, 'name' => 'Tehran']);
        $p2 = Province::factory()->create(['country_id' => $country->id, 'name' => 'Other']);

        $data = ['country_id' => $country->id, 'name' => 'Tehran'];
        $request = $this->makeRequest(UpdateProvinceRequest::class, $data, [
            'province_id' => $p2->id,
            'country_id'  => $country->id,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_same_name_for_same_record_passes(): void
    {
        $country = Country::factory()->create();
        $p1 = Province::factory()->create(['country_id' => $country->id, 'name' => 'Tehran']);

        $data = ['country_id' => $country->id, 'name' => 'Tehran'];
        $request = $this->makeRequest(UpdateProvinceRequest::class, $data, [
            'province_id' => $p1->id,
            'country_id'  => $country->id,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertFalse($validator->fails());
    }
}
