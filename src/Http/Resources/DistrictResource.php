<?php

namespace JobMetric\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed name
 * @property mixed subtitle
 * @property mixed keywords
 * @property mixed status
 *
 * @property mixed country
 * @property mixed province
 * @property mixed city
 */
class DistrictResource extends JsonResource
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
            'subtitle' => $this->subtitle,
            'keywords' => $this->keywords,
            'status' => $this->status,

            'country' => $this->whenLoaded('country', function () {
                return CountryResource::make($this->country);
            }),
            'province' => $this->whenLoaded('province', function () {
                return ProvinceResource::make($this->province);
            }),
            'city' => $this->whenLoaded('city', function () {
                return CityResource::make($this->city);
            }),
        ];
    }
}
