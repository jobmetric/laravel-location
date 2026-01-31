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
        "check_exist_name" => "ویژگی :attribute قبلا استفاده شده است.",
        "object_not_found" => ":name یافت نشد.",
        "model_not_use_trait" => ":model از ویژگی استفاده نمی کند.",
        "duplicate_address" => "این آدرس قبلا ثبت شده است.",
        "address_already_exists" => "آدرس تکراری است و نمی‌تواند دوباره ثبت شود.",
        "address_keys_only" => "فیلد آدرس فقط می‌تواند شامل کلیدهای مجاز باشد: :allowed. کلیدهای نامعتبر: :invalid.",
        "info_keys_only" => "فیلد info فقط می‌تواند شامل کلیدهای مجاز باشد: :allowed. کلیدهای نامعتبر: :invalid.",
        "province_and_city_required" => "ارسال استان (province_id) و شهر (city_id) الزامی است.",
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
        "location" => "موقعیت",
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

    "events" => [
        "country_deleted" => [
            "title" => "حذف کشور",
            "description" => "هنگامی که یک کشور حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "country_force_deleted" => [
            "title" => "حذف اجباری کشور",
            "description" => "هنگامی که یک کشور به صورت اجباری حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "country_restored" => [
            "title" => "بازیابی کشور",
            "description" => "هنگامی که یک کشور بازیابی می‌شود، این رویداد فعال می‌شود.",
        ],

        "country_stored" => [
            "title" => "ذخیره کشور",
            "description" => "هنگامی که یک کشور ذخیره می‌شود، این رویداد فعال می‌شود.",
        ],

        "country_updated" => [
            "title" => "به‌روزرسانی کشور",
            "description" => "هنگامی که یک کشور به‌روزرسانی می‌شود، این رویداد فعال می‌شود.",
        ],

        "province_deleted" => [
            "title" => "حذف استان",
            "description" => "هنگامی که یک استان حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "province_force_deleted" => [
            "title" => "حذف اجباری استان",
            "description" => "هنگامی که یک استان به صورت اجباری حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "province_restored" => [
            "title" => "بازیابی استان",
            "description" => "هنگامی که یک استان بازیابی می‌شود، این رویداد فعال می‌شود.",
        ],

        "province_stored" => [
            "title" => "ذخیره استان",
            "description" => "هنگامی که یک استان ذخیره می‌شود، این رویداد فعال می‌شود.",
        ],

        "province_updated" => [
            "title" => "به‌روزرسانی استان",
            "description" => "هنگامی که یک استان به‌روزرسانی می‌شود، این رویداد فعال می‌شود.",
        ],

        "city_deleted" => [
            "title" => "حذف شهر",
            "description" => "هنگامی که یک شهر حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "city_force_deleted" => [
            "title" => "حذف اجباری شهر",
            "description" => "هنگامی که یک شهر به صورت اجباری حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "city_restored" => [
            "title" => "بازیابی شهر",
            "description" => "هنگامی که یک شهر بازیابی می‌شود، این رویداد فعال می‌شود.",
        ],

        "city_stored" => [
            "title" => "ذخیره شهر",
            "description" => "هنگامی که یک شهر ذخیره می‌شود، این رویداد فعال می‌شود.",
        ],

        "city_updated" => [
            "title" => "به‌روزرسانی شهر",
            "description" => "هنگامی که یک شهر به‌روزرسانی می‌شود، این رویداد فعال می‌شود.",
        ],

        "district_deleted" => [
            "title" => "حذف منطقه",
            "description" => "هنگامی که یک منطقه حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "district_force_deleted" => [
            "title" => "حذف اجباری منطقه",
            "description" => "هنگامی که یک منطقه به صورت اجباری حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "district_restored" => [
            "title" => "بازیابی منطقه",
            "description" => "هنگامی که یک منطقه بازیابی می‌شود، این رویداد فعال می‌شود.",
        ],

        "district_stored" => [
            "title" => "ذخیره منطقه",
            "description" => "هنگامی که یک منطقه ذخیره می‌شود، این رویداد فعال می‌شود.",
        ],

        "district_updated" => [
            "title" => "به‌روزرسانی منطقه",
            "description" => "هنگامی که یک منطقه به‌روزرسانی می‌شود، این رویداد فعال می‌شود.",
        ],

        "geo_area_deleted" => [
            "title" => "حذف منطقه جغرافیایی",
            "description" => "هنگامی که یک منطقه جغرافیایی حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "geo_area_force_deleted" => [
            "title" => "حذف اجباری منطقه جغرافیایی",
            "description" => "هنگامی که یک منطقه جغرافیایی به صورت اجباری حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "geo_area_restored" => [
            "title" => "بازیابی منطقه جغرافیایی",
            "description" => "هنگامی که یک منطقه جغرافیایی بازیابی می‌شود، این رویداد فعال می‌شود.",
        ],

        "geo_area_stored" => [
            "title" => "ذخیره منطقه جغرافیایی",
            "description" => "هنگامی که یک منطقه جغرافیایی ذخیره می‌شود، این رویداد فعال می‌شود.",
        ],

        "geo_area_updated" => [
            "title" => "به‌روزرسانی منطقه جغرافیایی",
            "description" => "هنگامی که یک منطقه جغرافیایی به‌روزرسانی می‌شود، این رویداد فعال می‌شود.",
        ],

        "address_deleted" => [
            "title" => "حذف آدرس",
            "description" => "هنگامی که یک آدرس حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "address_force_deleted" => [
            "title" => "حذف اجباری آدرس",
            "description" => "هنگامی که یک آدرس به صورت اجباری حذف می‌شود، این رویداد فعال می‌شود.",
        ],

        "address_restored" => [
            "title" => "بازیابی آدرس",
            "description" => "هنگامی که یک آدرس بازیابی می‌شود، این رویداد فعال می‌شود.",
        ],

        "address_stored" => [
            "title" => "ذخیره آدرس",
            "description" => "هنگامی که یک آدرس ذخیره می‌شود، این رویداد فعال می‌شود.",
        ],

        "address_updated" => [
            "title" => "به‌روزرسانی آدرس",
            "description" => "هنگامی که یک آدرس به‌روزرسانی می‌شود، این رویداد فعال می‌شود.",
        ],

        "location_stored" => [
            "title" => "ذخیره موقعیت",
            "description" => "هنگامی که یک موقعیت ذخیره می‌شود، این رویداد فعال می‌شود.",
        ],
    ],

];
