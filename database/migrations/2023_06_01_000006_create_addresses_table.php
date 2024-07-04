<?php

namespace JobMetric\Location\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('location.tables.address'), function (Blueprint $table) {
            $table->id();

            $table->morphs('addressable');
            /**
             * The addressable field is used to store the addressable of the address.
             */

            $table->foreignId('location_country_id')->index()->constrained(config('location.tables.country'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_country_id field is used to store the location country id of the address.
             */

            $table->foreignId('location_province_id')->index()->constrained(config('location.tables.province'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_province_id field is used to store the location province id of the address.
             */

            $table->foreignId('location_city_id')->index()->constrained(config('location.tables.city'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_city_id field is used to store the location city id of the address.
             */

            $table->foreignId('location_district_id')->nullable()->index()->constrained(config('location.tables.district'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_district_id field is used to store the location district id of the address.
             */

            $table->string('address')->index();
            /**
             * The address field is used to store the address of the address.
             */
            $table->string('pluck', 10)->nullable()->index();
            /**
             * The pluck field is used to store the pluck of the address.
             */
            $table->string('unit', 20)->nullable()->index();
            /**
             * The unit field is used to store the unit of the address.
             */
            $table->string('postcode', 20)->nullable()->index();
            /**
             * The postcode field is used to store the postcode of the address.
             */

            $table->double('lat', 20)->nullable();
            $table->double('lng', 20)->nullable();
            /**
             * The lat and lng fields are used to store the latitude and longitude of the address.
             */

            $table->json('info')->nullable();
            /**
             * The info field is used to store the info of the address.
             *
             * @example
             * [
             *    'mobile_prefix' => 'value',
             *    'mobile' => 'value',
             *    'name' => 'value',
             * ]
             */

            $table->softDeletes();
            /**
             * The deleted_at field is used to store the deleted at of the province.
             */

            $table->timestamps();
            /**
             * The created_at and updated_at fields are used to store the timestamps of the province.
             */
        });

        cache()->forget('location-address');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.address'));

        cache()->forget('location-address');
    }
};
