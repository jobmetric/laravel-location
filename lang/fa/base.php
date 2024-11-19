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
        "errors" => "خطای اعتبارسنجی رخ داده است.",
        "check_exist_name" => "ویژگی :attribute قبلا استفاده شده است.",
        "object_not_found" => ":name یافت نشد.",
        "model_not_use_trait" => ":model از ویژگی استفاده نمی کند.",
    ],

    "messages" => [
        "found" => ":name با موفقیت یافت شد.",
        "created" => ":name با موفقیت ایجاد شد.",
        "updated" => ":name با موفقیت به روز شد.",
        "deleted" => ":name با موفقیت حذف شد.",
        "restored" => ":name با موفقیت بازیابی شد.",
        "permanently_deleted" => ":name با موفقیت برای همیشه حذف شد.",
        "deleted_items" => "{1} یک :name با موفقیت حذف شد.|[2,*] :count مورد :name با موفقیت حذف شدند.",
        "status" => [
            "enable" => "{1} یک :name فعال شد.|[2,*] :count مورد :name فعال شدند.",
            "disable" => "{1} یک :name غیرفعال شد.|[2,*] :count مورد :name غیرفعال شدند.",
        ],
    ],

    "model_name" => [
        "country" => "کشور",
        "province" => "استان",
        "city" => "شهر",
        "district" => "منطقه",
        "geo_area" => "منطقه جغرافیایی",
        "address" => "آدرس",
    ],

    "location_country" => [
        "name" => "کشور ها",
    ],

    "list" => [
        "location_country" => [
            "filters" => [
                "name" => [
                    "title" => "نام",
                    "placeholder" => "نام کشور را وارد کنید.",
                ],
            ],
            "columns" => [
                "flag" => "پرچم",
                "mobile_prefix" => "پیش شماره",
            ],
        ],
    ],

    "form" => [
        "location_country" => [
            "create" => [
                "title" => "ایجاد کشور",
            ],
            "edit" => [
                "title" => "ویرایش کشور",
            ],
            "fields" => [
                "name" => [
                    "title" => "نام",
                    "placeholder" => "نام کشور را وارد کنید.",
                ],
                "mobile_prefix" => [
                    "title" => "پیش شماره",
                    "placeholder" => "پیش شماره کشور را وارد کنید.",
                ],
                "validation" => [
                    "title" => "اعتبارسنجی",
                    "placeholder" => "اعتبارسنجی کشور را وارد کنید.",
                ],
                "flag" => [
                    "title" => "پرچم",
                    "placeholder" => "پرچم کشور را انتخاب کنید.",
                ],
            ],
        ],
    ],

];
