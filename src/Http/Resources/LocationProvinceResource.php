<?php

namespace JobMetric\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed name
 * @property mixed flag
 * @property mixed mobile_prefix
 * @property mixed validation
 * @property mixed status
 *
 * @property mixed country
 * @property mixed cities
 * @property mixed districts
 */
class LocationProvinceResource extends JsonResource
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
            'name' => $this->name,
            'status' => $this->status,

            'country' => $this->whenLoaded('country', function () {
                return LocationCountryResource::make($this->country);
            }),
            'cities' => $this->whenLoaded('cities', function () {
                return LocationCityResource::collection($this->cities);
            }),
            'districts' => $this->whenLoaded('districts', function () {
                return LocationDistrictResource::collection($this->districts);
            }),
        ];
    }
}
