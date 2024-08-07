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
        Schema::create(config('location.tables.province'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('location_country_id')->index()->constrained(config('location.tables.country'))->cascadeOnDelete()->cascadeOnUpdate();
            /**
             * The location_country_id field is used to store the location country id of the province.
             */

            $table->string('name', 150)->nullable()->index();
            /**
             * The name field is used to store the name of the province.
             */

            $table->boolean('status')->default(true);
            /**
             * The status field is used to store the status of the province.
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

        cache()->forget('location-province');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.province'));

        cache()->forget('location-province');
    }
};
