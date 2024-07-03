<?php

namespace JobMetric\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed geoArea
 * @property mixed country
 * @property mixed province
 * @property mixed city
 * @property mixed district
 */
class LocationGeoAreaZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'geo_area' => $this->whenLoaded('geoArea', LocationGeoAreaResource::make($this->geoArea)),

            'country' => $this->whenLoaded('country', LocationCountryResource::make($this->country)),
            'province' => $this->whenLoaded('province', LocationProvinceResource::make($this->province)),
            'city' => $this->whenLoaded('city', LocationCityResource::make($this->city)),
            'district' => $this->whenLoaded('district', LocationDistrictResource::make($this->district)),
        ];
    }
}
