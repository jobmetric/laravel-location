<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\Country;

use JobMetric\Location\Http\Requests\Country\StoreCountryRequest;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class StoreCountryRequestTest extends RequestTestCase
{
    public function test_valid_payload_passes(): void
    {
        $data = [
            'name'   => 'Iran',
            'status' => true,
        ];

        $request = $this->makeRequest(StoreCountryRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
        $this->assertNotEmpty($request->attributes());
    }

    public function test_missing_name_fails(): void
    {
        $data = [];

        $request = $this->makeRequest(StoreCountryRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_duplicate_name_fails(): void
    {
        Country::factory()->setName('Iran')->create();

        $data = ['name' => 'Iran'];

        $request = $this->makeRequest(StoreCountryRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
