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
class AddressResource extends JsonResource
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

            'address' => $this->address,
            'postcode' => $this->postcode,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'info' => $this->info,
            'full_address' => $this->full_address,
            'address_for_letter' => $this->address_for_letter,

            'parent' => $this->whenLoaded('parent', function () {
                return AddressResource::make($this->parent);
            }),
            'child' => $this->whenLoaded('child', function () {
                return AddressResource::make($this->child);
            }),

            'history' => $this->when($this->parent_id !== null, function () {
                return $this->getHistoryChain();
            }),

            'owner' => $this?->owner_resource,
        ];
    }

    /**
     * Get the complete history chain of address versions.
     * Returns all parent addresses recursively, from oldest to newest.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getHistoryChain(): array
    {
        $history = [];
        
        // Load parent if not already loaded
        if ($this->parent_id && ! $this->relationLoaded('parent')) {
            $this->load('parent');
        }
        
        $current = $this->parent;

        while ($current) {
            $history[] = [
                'id' => $current->id,
                'parent_id' => $current->parent_id,
                'address' => $current->address,
                'postcode' => $current->postcode,
                'lat' => $current->lat,
                'lng' => $current->lng,
                'info' => $current->info,
                'full_address' => $current->full_address,
                'address_for_letter' => $current->address_for_letter,
                'created_at' => $current->created_at?->toDateTimeString(),
                'deleted_at' => $current->deleted_at?->toDateTimeString(),
            ];

            // Load parent if not already loaded
            if ($current->parent_id && ! $current->relationLoaded('parent')) {
                $current->load('parent');
            }

            $current = $current->parent;
        }

        // Reverse to show oldest first
        return array_reverse($history);
    }
}
