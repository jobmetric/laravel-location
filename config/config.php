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
        'country' => 'location_countries',
        'province' => 'location_provinces',
        'city' => 'location_cities',
        'district' => 'location_districts',
        'geo_area' => 'location_geo_areas',
        'geo_area_zone' => 'location_geo_area_zones',
        'address' => 'location_addresses',
    ],

    /*
    |--------------------------------------------------------------------------
    | Foreign Key
    |--------------------------------------------------------------------------
    |
    | Foreign key in database
    */

    "foreign_key" => [
        'country' => 'location_country_id',
        'province' => 'location_province_id',
        'city' => 'location_city_id',
        'district' => 'location_district_id',
        'geo_area' => 'geo_area_id',
        'address' => 'address_id',
    ],

];
