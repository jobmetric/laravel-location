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
        Schema::create(config('location.tables.city'), function (Blueprint $table) {
            $table->id();

            $table->foreignId(config('location.foreign_key.country'))->index()->constrained(config('location.tables.country'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_country_id field is used to store the location country id of the city.
             */

            $table->foreignId(config('location.foreign_key.province'))->index()->constrained(config('location.tables.province'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_province_id field is used to store the location province id of the city.
             */

            $table->string('name', 150)->nullable()->index();
            /**
             * The name field is used to store the name of the city.
             */

            $table->boolean('status')->default(true);
            /**
             * The status field is used to store the status of the city.
             */
        });

        cache()->forget('location-city');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.city'));

        cache()->forget('location-city');
    }
};