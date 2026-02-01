<?php

namespace JobMetric\Location\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use JobMetric\Location\Models\GeoAreaRelation;
use JobMetric\Location\Models\Location;
use JobMetric\Location\Models\LocationRelation;
use JobMetric\Translation\Http\Resources\TranslationCollectionResource;

/**
 * Class GeoAreaResource
 *
 * Transforms the GeoArea model into a structured JSON resource.
 *
 * @property int $id
 * @property bool $status
 * @property Carbon|null $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read LocationRelation[] $locationRelations
 * @property-read Location[] $locations
 * @property-read GeoAreaRelation[] $geoAreaRelations
 */
class GeoAreaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'translations' => $this->whenLoaded('translations', function () {
                return TranslationCollectionResource::make($this)->withLocale(app()->getLocale());
            }),

            'status'     => (bool) $this->status,

            // ISO 8601 timestamps for interoperability across clients
            'deleted_at' => $this->deleted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Loaded relations - locations through locationRelations
            'locations'  => $this->whenLoaded('locationRelations', function () {
                return $this->locationRelations->map(function (LocationRelation $relation) {
                    return $relation->relationLoaded('location') ? LocationResource::make($relation->location) : [
                        'location_id' => $relation->location_id,
                    ];
                });
            }),
        ];
    }
}
