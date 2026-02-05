<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base Location Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during Location for
    | various messages that we need to display to the user.
    |
    */

    "validation" => [
        "check_exist_name" => "The :attribute has already been taken.",
        "object_not_found" => "The :name not found.",
        "model_not_use_trait" => "The :model does not use the trait.",
        "duplicate_address" => "This address has already been registered.",
        "address_already_exists" => "Duplicate address cannot be registered again.",
        "address_keys_only" => "The address field may only contain the allowed keys: :allowed. Invalid keys: :invalid.",
        "info_keys_only" => "The info field may only contain the allowed keys: :allowed. Invalid keys: :invalid.",
        "province_and_city_required" => "Province (province_id) and city (city_id) are required.",
        "duplicate_location" => "Duplicate locations found in the list.",
        "address_owner_required" => "Address owner (owner_type and owner_id) is required.",
    ],

    "messages" => [
        "found" => "The :name was found successfully.",
        "created" => "The :name was created successfully.",
        "updated" => "The :name was updated successfully.",
        "deleted" => "The :name was deleted successfully.",
        "restored" => "The :name was restored successfully.",
        "permanently_deleted" => "The :name was permanently deleted successfully.",
    ],

    "model_name" => [
        "country" => "Country",
        "province" => "Province",
        "city" => "City",
        "district" => "District",
        "location" => "Location",
        "geo_area" => "Geo Area",
        "address" => "Address",
    ],

    "fields" => [
        "translation" => "Translation",
        "name" => "Name",
        "subtitle" => "Subtitle",
        "keywords" => "Keywords",
        "description" => "Description",
        "status" => "Status",
        "flag" => "Flag",
        "mobile_prefix" => "Mobile Prefix",
        "validation" => "Validation",
        "address_on_letter" => "Address On Letter",
        "country_id" => "Country",
        "province_id" => "Province",
        "city_id" => "City",
        "district_id" => "District",
        "locations" => "Locations",
        "address" => "Address",
        "postcode" => "Postcode",
        "lat" => "Latitude",
        "lng" => "Longitude",
        "info" => "Info",
    ],

    "location_country" => [
        "name" => "Countries",
    ],

    "list" => [
        "location_country" => [
            "filters" => [
                "name" => [
                    "title" => "Name",
                    "placeholder" => "Enter the country name.",
                ],
            ],
            "columns" => [
                "flag" => "Flag",
                "mobile_prefix" => "Mobile Prefix",
            ],
        ],
    ],

    "form" => [
        "location_country" => [
            "create" => [
                "title" => "Create Country",
            ],
            "edit" => [
                "title" => "Edit Country",
            ],
            "fields" => [
                "name" => [
                    "title" => "Name",
                    "placeholder" => "Enter the country name.",
                ],
                "mobile_prefix" => [
                    "title" => "Mobile Prefix",
                    "placeholder" => "Enter the mobile prefix of the country.",
                ],
                "validation" => [
                    "title" => "Validation",
                    "placeholder" => "Enter the validation of the country.",
                ],
                "flag" => [
                    "title" => "Flag",
                    "placeholder" => "Enter the flag of the country.",
                ],
            ],
        ],
    ],

    "events" => [
        "country_deleted" => [
            "title" => "Country Deleted",
            "description" => "This event is triggered when a Country is deleted.",
        ],

        "country_force_deleted" => [
            "title" => "Country Force Deleted",
            "description" => "This event is triggered when a Country is force deleted.",
        ],

        "country_restored" => [
            "title" => "Country Restored",
            "description" => "This event is triggered when a Country is restored.",
        ],

        "country_stored" => [
            "title" => "Country Stored",
            "description" => "This event is triggered when a Country is stored.",
        ],

        "country_updated" => [
            "title" => "Country Updated",
            "description" => "This event is triggered when a Country is updated.",
        ],

        "province_deleted" => [
            "title" => "Province Deleted",
            "description" => "This event is triggered when a Province is deleted.",
        ],

        "province_force_deleted" => [
            "title" => "Province Force Deleted",
            "description" => "This event is triggered when a Province is force deleted.",
        ],

        "province_restored" => [
            "title" => "Province Restored",
            "description" => "This event is triggered when a Province is restored.",
        ],

        "province_stored" => [
            "title" => "Province Stored",
            "description" => "This event is triggered when a Province is stored.",
        ],

        "province_updated" => [
            "title" => "Province Updated",
            "description" => "This event is triggered when a Province is updated.",
        ],

        "city_deleted" => [
            "title" => "City Deleted",
            "description" => "This event is triggered when a City is deleted.",
        ],

        "city_force_deleted" => [
            "title" => "City Force Deleted",
            "description" => "This event is triggered when a City is force deleted.",
        ],

        "city_restored" => [
            "title" => "City Restored",
            "description" => "This event is triggered when a City is restored.",
        ],

        "city_stored" => [
            "title" => "City Stored",
            "description" => "This event is triggered when a City is stored.",
        ],

        "city_updated" => [
            "title" => "City Updated",
            "description" => "This event is triggered when a City is updated.",
        ],

        "district_deleted" => [
            "title" => "District Deleted",
            "description" => "This event is triggered when a District is deleted.",
        ],

        "district_force_deleted" => [
            "title" => "District Force Deleted",
            "description" => "This event is triggered when a District is force deleted.",
        ],

        "district_restored" => [
            "title" => "District Restored",
            "description" => "This event is triggered when a District is restored.",
        ],

        "district_stored" => [
            "title" => "District Stored",
            "description" => "This event is triggered when a District is stored.",
        ],

        "district_updated" => [
            "title" => "District Updated",
            "description" => "This event is triggered when a District is updated.",
        ],

        "geo_area_deleted" => [
            "title" => "Geo Area Deleted",
            "description" => "This event is triggered when a Geo Area is deleted.",
        ],

        "geo_area_force_deleted" => [
            "title" => "Geo Area Force Deleted",
            "description" => "This event is triggered when a Geo Area is force deleted.",
        ],

        "geo_area_restored" => [
            "title" => "Geo Area Restored",
            "description" => "This event is triggered when a Geo Area is restored.",
        ],

        "geo_area_stored" => [
            "title" => "Geo Area Stored",
            "description" => "This event is triggered when a Geo Area is stored.",
        ],

        "geo_area_updated" => [
            "title" => "Geo Area Updated",
            "description" => "This event is triggered when a Geo Area is updated.",
        ],

        "address_deleted" => [
            "title" => "Address Deleted",
            "description" => "This event is triggered when an Address is deleted.",
        ],

        "address_force_deleted" => [
            "title" => "Address Force Deleted",
            "description" => "This event is triggered when an Address is force deleted.",
        ],

        "address_restored" => [
            "title" => "Address Restored",
            "description" => "This event is triggered when an Address is restored.",
        ],

        "address_stored" => [
            "title" => "Address Stored",
            "description" => "This event is triggered when an Address is stored.",
        ],

        "address_updated" => [
            "title" => "Address Updated",
            "description" => "This event is triggered when an Address is updated.",
        ],

        "location_stored" => [
            "title" => "Location Stored",
            "description" => "This event is triggered when a Location is stored.",
        ],
    ],

];
