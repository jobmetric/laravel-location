<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\District;

use JobMetric\Location\Http\Requests\District\UpdateDistrictRequest;
use JobMetric\Location\Models\District;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class UpdateDistrictRequestTest extends RequestTestCase
{
    public function test_empty_payload_passes(): void
    {
        $data = [];
        $request = $this->makeRequest(UpdateDistrictRequest::class, $data, [
            'district_id' => 1,
            'city_id'     => 1,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertFalse($validator->fails());
    }

    public function test_keywords_must_be_array_of_strings(): void
    {
        $data = ['keywords' => [123]];
        $request = $this->makeRequest(UpdateDistrictRequest::class, $data, [
            'district_id' => 1,
            'city_id'     => 1,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('keywords.0', $validator->errors()->toArray());
    }

    public function test_duplicate_name_in_same_city_fails(): void
    {
        $graph = $this->makeLocationGraph();

        $d1 = District::factory()->create(['city_id' => $graph['city']->id, 'name' => 'D1']);
        $d2 = District::factory()->create(['city_id' => $graph['city']->id, 'name' => 'Other']);

        $data = ['city_id' => $graph['city']->id, 'name' => 'D1'];
        $request = $this->makeRequest(UpdateDistrictRequest::class, $data, [
            'district_id' => $d2->id,
            'city_id'     => $graph['city']->id,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_same_name_for_same_record_passes(): void
    {
        $graph = $this->makeLocationGraph();

        $d1 = District::factory()->create(['city_id' => $graph['city']->id, 'name' => 'D1']);

        $data = ['city_id' => $graph['city']->id, 'name' => 'D1'];
        $request = $this->makeRequest(UpdateDistrictRequest::class, $data, [
            'district_id' => $d1->id,
            'city_id'     => $graph['city']->id,
        ]);

        $validator = $this->validate($request, $data);
        $this->assertFalse($validator->fails());
    }
}
