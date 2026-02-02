<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\Province;

use JobMetric\Location\Http\Requests\Province\StoreProvinceRequest;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\Province;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class StoreProvinceRequestTest extends RequestTestCase
{
    public function test_valid_payload_passes(): void
    {
        $country = Country::factory()->create();

        $data = [
            'country_id' => $country->id,
            'name'       => 'Tehran',
            'status'     => true,
        ];

        $request = $this->makeRequest(StoreProvinceRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
    }

    public function test_missing_country_id_fails(): void
    {
        $data = ['name' => 'Tehran'];
        $request = $this->makeRequest(StoreProvinceRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('country_id', $validator->errors()->toArray());
    }

    public function test_duplicate_name_in_same_country_fails(): void
    {
        $country = Country::factory()->create();
        Province::factory()->create(['country_id' => $country->id, 'name' => 'Tehran']);

        $data = ['country_id' => $country->id, 'name' => 'Tehran'];
        $request = $this->makeRequest(StoreProvinceRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
