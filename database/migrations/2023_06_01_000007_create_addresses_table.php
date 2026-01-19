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
        Schema::create(config('location.tables.address'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')
                ->nullable()
                ->index()
                ->constrained(config('location.tables.address'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->morphs('owner');
            /**
             * This field stores the address owner.
             *
             * e.g. User, Store, ...
             */

            $table->foreignId('country_id')
                ->index()
                ->constrained(config('location.tables.country'))
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('province_id')
                ->index()
                ->constrained(config('location.tables.province'))
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('city_id')
                ->index()
                ->constrained(config('location.tables.city'))
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('district_id')
                ->nullable()
                ->index()
                ->constrained(config('location.tables.district'))
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->json('address')->index();
            /**
             * The address field contains the following data, which is stored as JSON:
             *
             * {blvd}
             * {street}
             * {alley}
             * {number}
             * {floor}
             * {unit}
             */

            $table->string('postcode', 20)->nullable()->index();
            /**
             * Postal code of the desired address
             */

            $table->double('lat', 20)->nullable();
            $table->double('lng', 20)->nullable();
            /**
             * The lat and lng fields are used to store the latitude and longitude of the address.
             */

            $table->json('info')->nullable();
            /**
             * The information field is used to store additional address information.
             *
             * @example
             * {
             *    'mobile_prefix': 'value',
             *    'mobile': 'value',
             *    'name': 'value',
             * }
             */

            $table->softDeletes();
            $table->dateTime('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.address'));
    }
};
