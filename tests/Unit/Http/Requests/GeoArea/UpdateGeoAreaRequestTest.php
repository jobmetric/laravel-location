<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests\GeoArea;

use JobMetric\Location\Http\Requests\GeoArea\UpdateGeoAreaRequest;
use JobMetric\Location\Tests\Unit\Http\Requests\RequestTestCase;

class UpdateGeoAreaRequestTest extends RequestTestCase
{
    public function test_empty_payload_passes(): void
    {
        $data = [];
        $request = $this->makeRequest(UpdateGeoAreaRequest::class, $data, ['geo_area_id' => 1]);
        $validator = $this->validate($request, $data);

        $this->assertFalse($validator->fails());
    }

    public function test_translation_without_name_fails(): void
    {
        $data = [
            'translation' => [
                'en' => [
                    'description' => 'Desc',
                ],
            ],
        ];

        $request = $this->makeRequest(UpdateGeoAreaRequest::class, $data, ['geo_area_id' => 1]);
        $validator = $this->validate($request, $data);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('translation.en.name', $validator->errors()->toArray());
    }
}

