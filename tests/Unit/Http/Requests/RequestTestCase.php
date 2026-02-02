<?php

namespace JobMetric\Location\Tests\Unit\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use JobMetric\Location\Models\City;
use JobMetric\Location\Models\Country;
use JobMetric\Location\Models\District;
use JobMetric\Location\Models\Province;
use JobMetric\Location\Tests\TestCase;

abstract class RequestTestCase extends TestCase
{
    /**
     * Build a FormRequest instance with container + redirector.
     *
     * @template T of FormRequest
     * @param class-string<T> $requestClass
     * @param array<string,mixed> $data
     * @param array<string,mixed> $context
     *
     * @return T
     */
    protected function makeRequest(string $requestClass, array $data = [], array $context = []): FormRequest
    {
        /** @var FormRequest $request */
        $request = $requestClass::create('/', 'POST', $data);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));

        if (method_exists($request, 'setContext')) {
            $request->setContext($context);
        }

        return $request;
    }

    /**
     * Validate using the request's rules and attributes.
     *
     * @param FormRequest $request
     * @param array<string,mixed> $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validate(FormRequest $request, array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, $request->rules(), [], $request->attributes());
    }

    /**
     * Create a full location hierarchy (country -> province -> city -> district).
     *
     * @return array{country: Country, province: Province, city: City, district: District}
     */
    protected function makeLocationGraph(): array
    {
        $country = Country::factory()->create();
        $province = Province::factory()->create(['country_id' => $country->id]);
        $city = City::factory()->create(['province_id' => $province->id]);
        $district = District::factory()->create(['city_id' => $city->id]);

        return compact('country', 'province', 'city', 'district');
    }
}
