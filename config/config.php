<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Time
    |--------------------------------------------------------------------------
    |
    | Cache time for get data location
    |
    | - set zero for remove cache
    | - set null for forever
    |
    | - unit: minutes
    */

    "cache_time" => env("LOCATION_CACHE_TIME", 0),

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Table name in database
    */

    "tables" => [
        'country' => 'countries',
        'province' => 'provinces',
        'city' => 'cities',
        'district' => 'districts',
        'geo_area' => 'geo_areas',
        'address' => 'addresses',
    ],

];
