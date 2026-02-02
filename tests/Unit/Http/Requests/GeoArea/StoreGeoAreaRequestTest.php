<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\GeoArea;

use JobMetric\Location\Http\Requests\GeoArea\StoreGeoAreaRequest;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class StoreGeoAreaRequestTest extends RequestTestCase
{
    public function test_valid_payload_passes(): void
    {
        $graph = $this->makeLocationGraph();

        $data = [
            'translation' => [
                'en' => [
                    'name'        => 'Area',
                    'description' => 'Desc',
                ],
            ],
            'status'      => true,
            'locations'   => [
                [
                    'country_id' => $graph['country']->id,
                ],
            ],
        ];

        $request = $this->makeRequest(StoreGeoAreaRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
        $this->assertNotEmpty($request->attributes());
    }

    public function test_missing_translation_fails(): void
    {
        $data = [];

        $request = $this->makeRequest(StoreGeoAreaRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('translation', $validator->errors()->toArray());
    }

    public function test_duplicate_locations_fails(): void
    {
        $graph = $this->makeLocationGraph();

        $loc = [
            'country_id'  => $graph['country']->id,
            'province_id' => $graph['province']->id,
            'city_id'     => $graph['city']->id,
            'district_id' => $graph['district']->id,
        ];

        $data = [
            'translation' => [
                'en' => ['name' => 'Area', 'description' => 'Desc'],
            ],
            'locations'   => [$loc, $loc],
        ];

        $request = $this->makeRequest(StoreGeoAreaRequest::class, $data);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('locations', $validator->errors()->toArray());
    }
}
