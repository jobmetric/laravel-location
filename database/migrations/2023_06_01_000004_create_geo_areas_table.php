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
        Schema::create(config('location.tables.geo_area'), function (Blueprint $table) {
            $table->id();

            $table->string('title');
            /**
             * The title field is used to store the title of the district.
             */

            $table->string('description')->nullable();
            /**
             * The description field is used to store the description of the district.
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

        cache()->forget('location-geo-area');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.geo_area'));

        cache()->forget('location-geo-area');
    }
};
