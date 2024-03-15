<?php

namespace JobMetric\Location\Tests;

use JobMetric\Location\Facades\LocationCountry;
use Tests\BaseDatabaseTestCase as BaseTestCase;

class LocationCountryTest extends BaseTestCase
{
    /**
     * A basic test example.
     */
    public function testStoreCountry(): void
    {
        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        $this->assertIsArray($locationCountry);
        $this->assertTrue($locationCountry['ok']);
        $this->assertIsInt($locationCountry['data']->id);
        $this->assertEquals('Iran', $locationCountry['data']->name);
        $this->assertNull($locationCountry['data']->flag);
        $this->assertNull($locationCountry['data']->mobile_prefix);
        $this->assertNull($locationCountry['data']->validation);
        $this->assertTrue($locationCountry['data']->status);

        $locationCountry = LocationCountry::store([
            'name' => 'Iran',
        ]);

        $this->assertIsArray($locationCountry);
        $this->assertFalse($locationCountry['ok']);
        $this->assertIsArray($locationCountry['errors']);

        $locationCountry = LocationCountry::store([
            'name' => 'Turkey',
            'flag' => 'tr',
            'mobile_prefix' => 90,
            'validation' => [
                'phone' => '09',
                'mobile' => '05',
            ],
            'status' => false,
        ]);

        $this->assertIsArray($locationCountry);
        $this->assertTrue($locationCountry['ok']);
        $this->assertIsInt($locationCountry['data']->id);
        $this->assertEquals('Turkey', $locationCountry['data']->name);
        $this->assertEquals('tr', $locationCountry['data']->flag);
        $this->assertEquals(90, $locationCountry['data']->mobile_prefix);
        $this->assertIsArray($locationCountry['data']->validation);
        $this->assertFalse($locationCountry['data']->status);
    }
}
