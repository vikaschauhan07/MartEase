<?php

namespace App\Helpers;


class ProjectConstants
{
    const EMAIL_NOT_VERIFIED = 0;
    const EMAIL_VERIFIED = 1;

    const PHONE_NOT_VERIFIED = 0;
    const PHONE_VERIFIED = 1;

    const EMAIL_OTP = 1;
    const PHONE_OTP = 2;

    const USER_ACTIVE = 1;
    const USER_INACTIVE = 0;

    // Package status
    const EXTRA_SMALL_PACKAGE = 1;
    const SMALL_PACKAGE = 2;
    const MEDIUM_PACKAGE = 3;
    const LARAGE_PACKAGE = 4;

    const EXTRA_SMALL_PACKAGE_NAME = "EXTRA SMALL";
    const SMALL_PACKAGE_NAME = "SMALL";
    const MEDIUM_PACKAGE_NAME = "MEDIUM";
    const LARAGE_PACKAGE_NAME = "LARGE";

    const PACKAGE_NAME_ARRAY = [
        ProjectConstants::EXTRA_SMALL_PACKAGE => ProjectConstants::EXTRA_SMALL_PACKAGE_NAME,
        ProjectConstants::SMALL_PACKAGE => ProjectConstants::SMALL_PACKAGE_NAME,
        ProjectConstants::MEDIUM_PACKAGE => ProjectConstants::MEDIUM_PACKAGE_NAME,
        ProjectConstants::LARAGE_PACKAGE => ProjectConstants::LARAGE_PACKAGE_NAME,
    ];


    // API Status Codes
    const SERVER_ERROR = 500;
    const SUCCESS = 200;
    const SUCCESS_WITH_CONDITION = 201;
    const BAD_REQUEST = 400;
    const NOT_FOUND = 404;
    const CONFLICTS = 417;
    const ALREADY_EXIST = 409;
    const UNAUTHENTICATED = 401;
    const UNAUTHORIZED = 403;
    const VALIDATION_ERROR =  422;

    // file save Path
    const ADMIN_PROFILE = "admin/profile/";
    const PACKAGE_IMAGES = "package/images/";
    const DRIVER_DOCUMNETS = "driver/documnets/";
    const USER_PROFILE = 'user/profile/';
    
    const DRIVER_EMAIL_VERIFIED = 1;
    const DRIVER_LICENSE_ADDED = 2;
    const DRIVER_VHECLE_REGISTRATION_ADDED = 3;
    const DRIVER_VHECLE_INSURANCE_ADDED = 4;

     //Driver Documnets
    const DRIVING_LICENCE_FRONT = 1;
    const DRIVING_LICENCE_BACK = 2;
    const VEHICLE_REGISTRATION = 3;
    const VEHICLE_INSURANCE = 4;

    // Time Slots Array
    const TIME_SLOTS = [
        1 => "00:00-02:00",
        2 => "01:00-03:00",
        3 => "02:00-04:00",
        4 => "03:00-05:00",
        5 => "04:00-06:00",
        6 => "05:00-07:00",
        7 => "06:00-08:00",
        8 => "07:00-09:00",
        9 => "08:00-10:00",
        10 => "09:00-11:00",
        11 => "10:00-12:00",
        12 => "11:00-13:00",
        13 => "12:00-14:00",
        14 => "13:00-15:00",
        15 => "14:00-16:00",
        16 => "15:00-17:00",
        17 => "16:00-18:00",
        18 => "17:00-19:00",
        19 => "18:00-20:00",
        20 => "19:00-21:00",
        21 => "20:00-22:00",
        22 => "21:00-23:00",
        23 => "22:00-00:00",
        24 => "23:00-01:00"
    ];

    // City Array
    const CITY_ARRAY = [
        1 => "Calgary",
        2 => "Edmonton"
    ];
}
