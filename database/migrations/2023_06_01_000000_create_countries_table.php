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
        Schema::create(config('location.tables.country'), function (Blueprint $table) {
            $table->id();

            $table->string('name', 150)->index();
            /**
             * The name field is used to store the name of the country.
             */

            $table->string('flag')->nullable();
            /**
             * Must be selected from the list of flags available in the files.
             */

            $table->string('mobile_prefix', 20)->nullable()->index();
            /**
             * The country code is defined in this field.
             *
             * e.g. iran +98
             * e.g. USA  +1
             * e.g. United Kingdom +44
             * e.g. Israel +972
             */

            $table->json('validation')->nullable();
            /**
             * The validation field is used to store country number validation.
             * regex type for this field per country.
             */

            $table->string('address_on_letter')->nullable();
            /**
             * In this field, the address on the letter with the following words is used
             * with any format of a text to print the address in that country.
             *
             * {country}
             * {province}
             * {city}
             * {district}
             * {blvd}
             * {street}
             * {alley}
             * {number}
             * {floor}
             * {unit}
             * {receiver_number}
             * {receiver_name}
             * {postcode}
             */

            $table->boolean('status')->default(true)->index();
            /**
             * active status of this country.
             *
             * - true = active
             * - false = inactive
             */

            $table->softDeletes();
            $table->timestamps();

            $table->index([
                'name',
                'status',
                'deleted_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.country'));
    }
};
