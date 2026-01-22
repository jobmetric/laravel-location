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
             * The name of the country.
             *
             * e.g. Iran, United States, United Kingdom
             */

            $table->string('flag')->nullable();
            /**
             * Flag identifier for the country.
             *
             * Must be selected from the list of flags available in the files.
             * Typically stored as filename (e.g., "iran.svg", "usa.svg")
             */

            $table->unsignedInteger('mobile_prefix')->nullable()->index();
            /**
             * International mobile prefix (country calling code).
             *
             * e.g. Iran: 98
             * e.g. USA: 1
             * e.g. United Kingdom: 44
             * e.g. Israel: 972
             */

            $table->json('validation')->nullable();
            /**
             * Mobile number validation rules for this country.
             *
             * value: json
             * use: array of regex patterns
             * e.g. ["/^9\d{9}$/"] for Iran
             */

            $table->string('address_on_letter')->nullable();
            /**
             * Address format template for printing addresses in this country.
             *
             * Supports placeholders:
             * {country}, {province}, {city}, {district}
             * {blvd}, {street}, {alley}, {number}
             * {floor}, {unit}, {receiver_number}, {receiver_name}, {postcode}
             *
             * e.g. "{country}, {province}, {city}\n{district}, {blvd}, {street}"
             */

            $table->boolean('status')->default(true)->index();
            /**
             * Active status of this country.
             *
             * - true = active (available for selection)
             * - false = inactive (hidden from selection)
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
