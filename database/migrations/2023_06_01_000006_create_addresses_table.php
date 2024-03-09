<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JobMetric\BanIp\Enums\TableBanIpFieldTypeEnum;

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

            $table->foreignId(config('location.foreign_key.country'))->index()->constrained(config('location.tables.country'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_country_id field is used to store the location country id of the address.
             */

            $table->foreignId(config('location.foreign_key.province'))->index()->constrained(config('location.tables.province'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_province_id field is used to store the location province id of the address.
             */

            $table->foreignId(config('location.foreign_key.city'))->index()->constrained(config('location.tables.city'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_city_id field is used to store the location city id of the address.
             */

            $table->foreignId(config('location.foreign_key.district'))->index()->constrained(config('location.tables.district'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_district_id field is used to store the location district id of the address.
             */

            $table->string('address')->nullable();
            /**
             * The address field is used to store the address of the address.
             */
            $table->string('pluck', 10)->nullable();
            /**
             * The pluck field is used to store the pluck of the address.
             */
            $table->string('unit', 20)->nullable();
            /**
             * The unit field is used to store the unit of the address.
             */
            $table->string('postcode', 20)->nullable();
            /**
             * The postcode field is used to store the postcode of the address.
             */

            $table->double('lat')->nullable();
            $table->double('lng')->nullable();
            /**
             * The lat and lng fields are used to store the latitude and longitude of the address.
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
