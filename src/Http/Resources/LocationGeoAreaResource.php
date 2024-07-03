<?php

namespace JobMetric\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed id
 * @property mixed title
 * @property mixed description
 * @property mixed status
 *
 * @property mixed geoAreaZones
 */
class LocationGeoAreaResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,

            'geo_area_zones' => $this->whenLoaded('geoAreaZones', LocationGeoAreaZoneResource::collection($this->geoAreaZones)),
        ];
    }
}
