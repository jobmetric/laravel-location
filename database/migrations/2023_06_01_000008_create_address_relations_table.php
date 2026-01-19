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
        Schema::create(config('location.tables.address_relation'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('address_id')
                ->index()
                ->constrained(config('location.tables.address'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->morphs('addressable');
            /**
             * A model that intends to use the address
             *
             * e.g. Order, Invoice, ...
             */

            $table->string('collection')->nullable()->index();
            /**
             * This field is here to indicate a special field for the address.
             * By default, it can have a null value.
             */

            $table->dateTime('created_at')->useCurrent();

            $table->unique([
                'address_id',
                'addressable_type',
                'addressable_id',
                'collection'
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
        Schema::dropIfExists(config('location.tables.address_relation'));
    }
};
