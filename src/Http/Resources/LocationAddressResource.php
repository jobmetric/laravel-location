<?php

namespace JobMetric\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed address
 * @property mixed pluck
 * @property mixed unit
 * @property mixed postcode
 * @property mixed info
 * @property mixed full_address
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

            'country' => $this->whenLoaded('country', LocationCountryResource::make($this->country)),
            'province' => $this->whenLoaded('province', LocationProvinceResource::make($this->province)),
            'city' => $this->whenLoaded('city', LocationCityResource::make($this->city)),
            'district' => $this->whenLoaded('district', LocationDistrictResource::make($this->district)),

            'address' => $this->address,
            'pluck' => $this->pluck,
            'unit' => $this->unit,
            'postcode' => $this->postcode,
            'info' => $this->info,
            'full_address' => $this->full_address,
        ];
    }
}
