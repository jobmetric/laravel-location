<?php

namespace JobMetric\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property int country_id
 * @property int|null province_id
 * @property int|null city_id
 * @property int|null district_id
 * @property mixed created_at
 *
 * @property mixed country
 * @property mixed province
 * @property mixed city
 * @property mixed district
 * @property mixed locationRelations
 */
class LocationResource extends JsonResource
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
            'country_id' => $this->country_id,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'district_id' => $this->district_id,
            'created_at' => $this->created_at,

            'country' => $this->whenLoaded('country', function () {
                return CountryResource::make($this->country);
            }),
            'province' => $this->whenLoaded('province', function () {
                return ProvinceResource::make($this->province);
            }),
            'city' => $this->whenLoaded('city', function () {
                return CityResource::make($this->city);
            }),
            'district' => $this->whenLoaded('district', function () {
                return DistrictResource::make($this->district);
            }),
            'location_relations' => $this->whenLoaded('locationRelations', function () {
                return $this->locationRelations;
            }),
        ];
    }
}
