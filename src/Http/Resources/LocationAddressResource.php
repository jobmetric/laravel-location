<?php

namespace JobMetric\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property int|null parent_id
 * @property string owner_type
 * @property int owner_id
 * @property object|null address
 * @property string|null postcode
 * @property string|null lat
 * @property string|null lng
 * @property array|null info
 * @property string full_address
 * @property string address_for_letter
 * @property mixed owner_resource
 *
 * @property mixed country
 * @property mixed province
 * @property mixed city
 * @property mixed district
 * @property mixed parent
 * @property mixed child
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
            'parent_id' => $this->parent_id,

            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,

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
            'postcode' => $this->postcode,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'info' => $this->info,
            'full_address' => $this->full_address,
            'address_for_letter' => $this->address_for_letter,

            'parent' => $this->whenLoaded('parent', function () {
                return LocationAddressResource::make($this->parent);
            }),
            'child' => $this->whenLoaded('child', function () {
                return LocationAddressResource::make($this->child);
            }),

            'owner' => $this?->owner_resource,
        ];
    }
}
