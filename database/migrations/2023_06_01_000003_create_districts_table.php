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
        Schema::create(config('location.tables.district'), function (Blueprint $table) {
            $table->id();

            $table->foreignId(config('location.foreign_key.country'))->index()->constrained(config('location.tables.country'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_country_id field is used to store the location country id of the district.
             */

            $table->foreignId(config('location.foreign_key.province'))->index()->constrained(config('location.tables.province'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_province_id field is used to store the location province id of the district.
             */

            $table->foreignId(config('location.foreign_key.city'))->index()->constrained(config('location.tables.city'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_city_id field is used to store the location city id of the district.
             */

            $table->string('name', 150)->nullable()->index();
            /**
             * The name field is used to store the name of the district.
             */

            $table->boolean('status')->default(true);
            /**
             * The status field is used to store the status of the district.
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

        cache()->forget('location-district');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.district'));

        cache()->forget('location-district');
    }
};
