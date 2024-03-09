<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use JobMetric\BanIp\Enums\TableBanIpFieldTypeEnum;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(config('location.tables.country'), function (Blueprint $table) {
            $table->id();

            $table->string('name', 150)->nullable()->index();
            /**
             * The name field is used to store the name of the country.
             */

            $table->string('flag')->nullable();
            /**
             * The flag field is used to store the flag of the country.
             */

            $table->string('mobile_prefix', 20)->nullable()->index();
            /**
             * The mobile_prefix field is used to store the mobile prefix of the country.
             */

            $table->json('validation')->nullable();
            /**
             * The validation field is used to store the validation of the country.
             */

            $table->boolean('status')->default(true)->index();
            /**
             * The status field is used to store the status of the country.
             */
        });

        cache()->forget('location-country');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(config('location.tables.country'));

        cache()->forget('location-country');
    }
};
