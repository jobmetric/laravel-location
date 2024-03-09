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
        Schema::create(config('location.tables.geo_area_zone'), function (Blueprint $table) {
            $table->foreignId(config('location.foreign_key.geo_area'))->constrained(config('location.tables.geo_area'))->cascadeOnDelete();
            /**
             * The geo_area_id field is used to store the geo area id of the geo area zone.
             */

            $table->foreignId(config('location.foreign_key.country'))->index()->constrained(config('location.tables.country'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_country_id field is used to store the location country id of the geo area zone.
             */
            $table->foreignId(config('location.foreign_key.province'))->nullable()->index()->constrained(config('location.tables.province'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_province_id field is used to store the location province id of the geo area zone.
             */
            $table->foreignId(config('location.foreign_key.city'))->nullable()->index()->constrained(config('location.tables.city'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_city_id field is used to store the location city id of the geo area zone.
             */
            $table->foreignId(config('location.foreign_key.district'))->nullable()->index()->constrained(config('location.tables.district'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_district_id field is used to store the location district id of the geo area zone.
             */
        });

        cache()->forget('location-geo-area-zone');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.geo_area_zone'));

        cache()->forget('location-geo-area-zone');
    }
};
