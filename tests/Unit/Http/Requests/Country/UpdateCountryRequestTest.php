<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\Country;

use JobMetric\Location\Http\Requests\Country\UpdateCountryRequest;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class UpdateCountryRequestTest extends RequestTestCase
{
    public function test_empty_payload_passes(): void
    {
        $data = [];
        $request = $this->makeRequest(UpdateCountryRequest::class, $data, ['country_id' => 1]);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
    }

    public function test_duplicate_name_for_other_record_fails(): void
    {
        $c1 = Country::factory()->setName('A')->create();
        $c2 = Country::factory()->setName('B')->create();

        $data = ['name' => 'A'];
        $request = $this->makeRequest(UpdateCountryRequest::class, $data, ['country_id' => $c2->id]);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_same_name_for_same_record_passes(): void
    {
        $c1 = Country::factory()->setName('A')->create();

        $data = ['name' => 'A'];
        $request = $this->makeRequest(UpdateCountryRequest::class, $data, ['country_id' => $c1->id]);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
    }
}
