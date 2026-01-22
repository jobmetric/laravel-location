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
        Schema::create(config('location.tables.location'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->index()
                ->constrained(config('location.tables.country'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            /**
             * Reference to the country.
             *
             * Required field - every location must have a country.
             */

            $table->foreignId('province_id')
                ->nullable()
                ->index()
                ->constrained(config('location.tables.province'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            /**
             * Reference to the province.
             *
             * Optional - may be null for countries without provinces.
             */

            $table->foreignId('city_id')
                ->nullable()
                ->index()
                ->constrained(config('location.tables.city'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            /**
             * Reference to the city.
             *
             * Optional - may be null for locations without cities.
             */

            $table->foreignId('district_id')
                ->nullable()
                ->index()
                ->constrained(config('location.tables.district'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            /**
             * Reference to the district.
             *
             * Optional - may be null for locations without districts.
             */

            $table->dateTime('created_at')->useCurrent();

            $table->unique([
                'country_id',
                'province_id',
                'city_id',
                'district_id',
            ], 'UNIQUE_LOCATION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.location'));
    }
};
