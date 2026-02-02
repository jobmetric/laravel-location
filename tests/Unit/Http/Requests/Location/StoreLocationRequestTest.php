<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\Location;

use JobMetric\Location\Http\Requests\Location\StoreLocationRequest;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class StoreLocationRequestTest extends RequestTestCase
{
    public function test_valid_payload_passes_with_only_country(): void
    {
        $graph = $this->makeLocationGraph();

        $data = [
            'country_id' => $graph['country']->id,
        ];

        $request = $this->makeRequest(StoreLocationRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
    }

    public function test_missing_country_id_fails(): void
    {
        $data = [];

        $request = $this->makeRequest(StoreLocationRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('country_id', $validator->errors()->toArray());
    }
}
