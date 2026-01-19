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
        Schema::create(config('location.tables.geo_area_zone'), function (Blueprint $table) {
            $table->foreignId('geo_area_id')
                ->index()
                ->constrained(config('location.tables.geo_area'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('country_id')
                ->index()
                ->constrained(config('location.tables.country'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('province_id')
                ->nullable()
                ->index()
                ->constrained(config('location.tables.province'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('city_id')
                ->nullable()
                ->index()
                ->constrained(config('location.tables.city'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('district_id')
                ->nullable()
                ->index()
                ->constrained(config('location.tables.district'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique([
                'geo_area_id',
                'country_id',
                'province_id',
                'city_id',
                'district_id',
            ], 'UNIQUE_GEO_AREA_ZONE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.geo_area_zone'));
    }
};
