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
        Schema::create(config('location.tables.city'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('province_id')
                ->index()
                ->constrained(config('location.tables.province'))
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('name', 150)->nullable()->index();
            /**
             * The name field is used to store the name of the city.
             */

            $table->boolean('status')->default(true);
            /**
             * active status of this city.
             *
             * - true = active
             * - false = inactive
             */

            $table->softDeletes();
            $table->timestamps();

            $table->index([
                'province_id',
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
        Schema::dropIfExists(config('location.tables.city'));
    }
};
