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
             * Polymorphic relation to the model that owns this address.
             *
             * e.g. User, Store, Company
             */

            $table->json('address')->index();
            /**
             * Address details stored as JSON.
             *
             * value: json
             * use: {
             *     "blvd": "string",
             *     "street": "string",
             *     "alley": "string",
             *     "number": "string",
             *     "floor": "string",
             *     "unit": "string"
             * }
             */

            $table->string('postcode', 20)->nullable()->index();
            /**
             * Postal/ZIP code of the address.
             *
             * Format varies by country (e.g., 12345-6789 for the USA, 1234567890 for Iran)
             */

            $table->double('lat', 20)->nullable();
            $table->double('lng', 20)->nullable();
            /**
             * Geographic coordinates of the address.
             *
             * - lat: latitude (decimal degrees, -90 to 90)
             * - lng: longitude (decimal degrees, -180 to 180)
             * Used for mapping and distance calculations
             */

            $table->json('info')->nullable();
            /**
             * Additional address information stored as JSON.
             *
             * value: json
             * use: {
             *     "mobile_prefix": "string",
             *     "mobile": "string",
             *     "name": "string",
             *     "landline": "string",
             *     "notes": "string"
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
