<?php

namespace JobMetric\Location\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('location.tables.location_relation'), function (Blueprint $table) {
            $table->foreignId('location_id')
                ->index()
                ->constrained(config('location.tables.location'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->morphs('locationable', 'LR_LOCATIONABLE_IDX');
            /**
             * Polymorphic relation to any model that can have a location.
             *
             * e.g. GeoArea, Address, ...
             */

            $table->dateTime('created_at')->useCurrent();

            $table->unique([
                'location_id',
                'locationable_type',
                'locationable_id',
            ], 'LR_LOC_LOCATIONABLE_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.location_relation'));
    }
};
