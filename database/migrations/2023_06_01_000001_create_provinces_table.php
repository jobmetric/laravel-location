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
        Schema::create(config('location.tables.province'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->index()
                ->constrained(config('location.tables.country'))
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('name', 150)->nullable()->index();
            /**
             * The name of the province.
             *
             * e.g. Tehran, Isfahan, Fars
             */

            $table->boolean('status')->default(true);
            /**
             * Active status of this province.
             *
             * - true = active (available for selection)
             * - false = inactive (hidden from selection)
             */

            $table->softDeletes();
            $table->timestamps();

            $table->index([
                'country_id',
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
        Schema::dropIfExists(config('location.tables.province'));
    }
};
