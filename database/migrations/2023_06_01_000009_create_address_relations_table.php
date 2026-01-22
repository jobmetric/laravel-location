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
            $table->foreignId('address_id')
                ->index()
                ->constrained(config('location.tables.address'))
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->morphs('addressable');
            /**
             * Polymorphic relation to any model that can use an address.
             *
             * e.g. Order, Invoice, Shipment
             */

            $table->string('collection')->nullable()->index();
            /**
             * Collection name for categorizing addresses.
             *
             * Allows multiple addresses per model with different purposes.
             * e.g. "billing", "shipping", "delivery", "return"
             * null = default/primary address
             */

            $table->dateTime('created_at')->useCurrent();

            $table->unique([
                'address_id',
                'addressable_type',
                'addressable_id',
                'collection',
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
