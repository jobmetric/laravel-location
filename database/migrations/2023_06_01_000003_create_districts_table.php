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
        Schema::create(config('location.tables.district'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('city_id')
                ->index()
                ->constrained(config('location.tables.city'))
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('name', 150)->nullable()->index();
            /**
             * The name of the district.
             *
             * e.g. District 1, District 2, Central District
             */

            $table->string('subtitle', 200)->nullable();
            /**
             * A short subtitle/label for the district.
             *
             * Example use-cases:
             * - Alternative display label
             * - UI subtitle
             * - Marketplace category subtitle
             */

            $table->json('keywords')->nullable();
            /**
             * Search keywords for the district (array of strings).
             *
             * This is useful for search/autocomplete and can be populated via datasets.
             */

            $table->boolean('status')->default(true);
            /**
             * Active status of this district.
             *
             * - true = active (available for selection)
             * - false = inactive (hidden from selection)
             */

            $table->softDeletes();
            $table->timestamps();

            $table->index([
                'city_id',
                'name',
                'subtitle',
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
        Schema::dropIfExists(config('location.tables.district'));
    }
};
