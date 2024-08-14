<?php

namespace JobMetric\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property string addressable_type
 * @property int addressable_id
 * @property string address
 * @property string pluck
 * @property string unit
 * @property string postcode
 * @property array info
 * @property string full_address
 * @property mixed addressable_resource
 *
 * @property mixed country
 * @property mixed province
 * @property mixed city
 * @property mixed district
 */
class LocationAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'addressable_type' => $this->addressable_type,
            'addressable_id' => $this->addressable_id,

            'country' => $this->whenLoaded('country', function () {
                return LocationCountryResource::make($this->country);
            }),
            'province' => $this->whenLoaded('province', function () {
                return LocationProvinceResource::make($this->province);
            }),
            'city' => $this->whenLoaded('city', function () {
                return LocationCityResource::make($this->city);
            }),
            'district' => $this->whenLoaded('district', function () {
                return LocationDistrictResource::make($this->district);
            }),

            'address' => $this->address,
            'pluck' => $this->pluck,
            'unit' => $this->unit,
            'postcode' => $this->postcode,
            'info' => $this->info,
            'full_address' => $this->full_address,

            'addressable' => $this?->addressable_resource
        ];
    }
}
