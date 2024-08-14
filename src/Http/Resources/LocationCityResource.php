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
 * @property mixed province
 * @property mixed districts
 */
class LocationCityResource extends JsonResource
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
            'province' => $this->whenLoaded('province', function () {
                return LocationProvinceResource::make($this->province);
            }),
            'districts' => $this->whenLoaded('districts', function () {
                return LocationDistrictResource::collection($this->districts);
            }),
        ];
    }
}
