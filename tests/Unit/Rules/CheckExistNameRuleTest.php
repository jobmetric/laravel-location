<?php

namespace JobMetric\Location\Tests\Unit\Rules;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\Province;
use JobMetric\Location\Rules\CheckExistNameRule;
use JobMetric\Location\Tests\TestCase;
use stdClass;

class CheckExistNameRuleTest extends TestCase
{
    public function test_country_duplicate_name_fails(): void
    {
        Country::factory()->setName('Iran')->create();

        $validator = Validator::make([
            'name' => 'Iran',
        ], [
            'name' => [
                new CheckExistNameRule(Country::class),
            ],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_country_same_name_when_excluding_same_id_passes(): void
    {
        $country = Country::factory()->setName('Iran')->create();

        $validator = Validator::make([
            'name' => 'Iran',
        ], [
            'name' => [
                new CheckExistNameRule(Country::class, $country->id),
            ],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_province_duplicate_name_in_same_country_fails_via_payload_scope(): void
    {
        $c1 = Country::factory()->create();
        $c2 = Country::factory()->create();

        Province::factory()->create([
            'country_id' => $c1->id,
            'name'       => 'Tehran',
        ]);

        // Same country => should fail (parent id derived from payload via DataAwareRule)
        $validatorSameCountry = Validator::make([
            'country_id' => $c1->id,
            'name'       => 'Tehran',
        ], [
            'name' => [
                new CheckExistNameRule(Province::class),
            ],
        ]);
        $this->assertTrue($validatorSameCountry->fails());

        // Different country => should pass
        $validatorOtherCountry = Validator::make([
            'country_id' => $c2->id,
            'name'       => 'Tehran',
        ], [
            'name' => [
                new CheckExistNameRule(Province::class),
            ],
        ]);
        $this->assertFalse($validatorOtherCountry->fails());
    }

    public function test_province_duplicate_name_in_same_country_fails_via_constructor_parent_id(): void
    {
        $country = Country::factory()->create();

        Province::factory()->create([
            'country_id' => $country->id,
            'name'       => 'Tehran',
        ]);

        $validator = Validator::make([
            'country_id' => $country->id,
            'name'       => 'Tehran',
        ], [
            'name' => [
                new CheckExistNameRule(Province::class, null, $country->id),
            ],
        ]);

        $this->assertTrue($validator->fails());
    }

    public function test_invalid_model_class_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CheckExistNameRule(stdClass::class))->validate('name', 'X', function (): void {
            // noop
        });
    }
}
