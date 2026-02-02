<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\Address;

use JobMetric\Location\Http\Requests\Address\StoreAddressRequest;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class StoreAddressRequestTest extends RequestTestCase
{
    public function test_valid_payload_passes(): void
    {
        $graph = $this->makeLocationGraph();
        $data = [
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'district_id' => $graph['district']->id,
            'address'     => [
                'street' => 'Valiasr',
                'number' => '10',
            ],
            'postcode'    => '1234567890',
            'info'        => [
                'name'          => 'Majid',
                'mobile_prefix' => '+98',
                'mobile'        => '9120000000',
            ],
        ];

        $request = $this->makeRequest(StoreAddressRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
        $this->assertNotEmpty($request->attributes());
    }

    public function test_invalid_address_keys_fails(): void
    {
        $graph = $this->makeLocationGraph();
        $data = [
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'address'     => [
                'street' => 'Valiasr',
                'BAD'    => 'x',
            ],
        ];

        $request = $this->makeRequest(StoreAddressRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('address', $validator->errors()->toArray());
    }

    public function test_invalid_info_keys_fails(): void
    {
        $graph = $this->makeLocationGraph();
        $data = [
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'address'     => [
                'street' => 'Valiasr',
            ],
            'info'        => [
                'name' => 'Majid',
                'BAD'  => 'x',
            ],
        ];

        $request = $this->makeRequest(StoreAddressRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('info', $validator->errors()->toArray());
    }
}
