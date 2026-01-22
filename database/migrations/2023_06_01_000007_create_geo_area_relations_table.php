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
        Schema::create(config('location.tables.geo_area_relation'), function (Blueprint $table) {
            $table->foreignId('geo_area_id')
                ->index()
                ->constrained(config('location.tables.geo_area'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->morphs('geographical');
            /**
             * Polymorphic relation to any model that can be associated with a geo area.
             *
             * e.g. Order, Invoice, ...
             */

            $table->dateTime('created_at')->useCurrent();

            $table->unique([
                'geo_area_id',
                'geographical_type',
                'geographical_id',
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
        Schema::dropIfExists(config('location.tables.geo_area_relation'));
    }
};
