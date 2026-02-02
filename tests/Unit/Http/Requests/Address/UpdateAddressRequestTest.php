<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\Address;

use JobMetric\Location\Http\Requests\Address\UpdateAddressRequest;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class UpdateAddressRequestTest extends RequestTestCase
{
    public function test_empty_payload_passes(): void
    {
        $data = [];

        $request = $this->makeRequest(UpdateAddressRequest::class, $data, ['address_id' => 1]);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
    }

    public function test_invalid_address_keys_fails_when_address_present(): void
    {
        $data = [
            'address' => [
                'street' => 'Valiasr',
                'BAD'    => 'x',
            ],
        ];

        $request = $this->makeRequest(UpdateAddressRequest::class, $data, ['address_id' => 1]);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('address', $validator->errors()->toArray());
    }
}
