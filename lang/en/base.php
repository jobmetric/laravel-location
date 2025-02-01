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
        "geo_area" => "Geo Area",
        "address" => "Address",
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

];
