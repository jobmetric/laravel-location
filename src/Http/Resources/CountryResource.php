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
 * @property mixed provinces
 * @property mixed cities
 * @property mixed districts
 */
class CountryResource extends JsonResource
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
            'flag' => $this->flag,
            'mobile_prefix' => $this->mobile_prefix,
            'validation' => $this->validation,
            'status' => $this->status,

            'provinces' => $this->whenLoaded('provinces', function () {
                return ProvinceResource::collection($this->provinces);
            }),
            'cities' => $this->whenLoaded('cities', function () {
                return CityResource::collection($this->cities);
            }),
            'districts' => $this->whenLoaded('districts', function () {
                return DistrictResource::collection($this->districts);
            }),
        ];
    }
}
