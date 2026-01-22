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
        Schema::create(config('location.tables.geo_area'), function (Blueprint $table) {
            $table->id();

            $table->boolean('status')->default(true);
            /**
             * Active status of this geo area.
             *
             * - true = active (available for use)
             * - false = inactive (hidden from selection)
             */

            $table->softDeletes();
            $table->timestamps();

            $table->index([
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
        Schema::dropIfExists(config('location.tables.geo_area'));
    }
};
