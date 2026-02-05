<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\District;

use JobMetric\Location\Http\Requests\District\StoreDistrictRequest;
use JobMetric\Location\Models\District;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class StoreDistrictRequestTest extends RequestTestCase
{
    public function test_valid_payload_passes(): void
    {
        $graph = $this->makeLocationGraph();

        $data = [
            'city_id' => $graph['city']->id,
            'name'    => 'District 1',
            'subtitle' => 'Central',
            'keywords' => ['central', 'downtown'],
            'status'  => true,
        ];

        $request = $this->makeRequest(StoreDistrictRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
    }

    public function test_keywords_must_be_array_of_strings(): void
    {
        $graph = $this->makeLocationGraph();

        $data = [
            'city_id' => $graph['city']->id,
            'name'    => 'District 1',
            'keywords' => [123],
        ];

        $request = $this->makeRequest(StoreDistrictRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('keywords.0', $validator->errors()->toArray());
    }

    public function test_missing_city_id_fails(): void
    {
        $data = ['name' => 'District 1'];

        $request = $this->makeRequest(StoreDistrictRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('city_id', $validator->errors()->toArray());
    }

    public function test_duplicate_name_in_same_city_fails(): void
    {
        $graph = $this->makeLocationGraph();

        District::factory()->create([
            'city_id' => $graph['city']->id,
            'name'    => 'D1',
        ]);

        $data = ['city_id' => $graph['city']->id, 'name' => 'D1'];

        $request = $this->makeRequest(StoreDistrictRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
