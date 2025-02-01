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

    const USER_ONLINE = 1;
    const USER_OFFLINE = 0;

    const CHAT_INACTIVE = 0;
    const CHAT_ACTIVE = 1;

    const MESSAGE_UNREAD = 0;
    const MESSAGE_READ = 1;


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
    const BLOG_FILE = 'blog/files/';
    const POST_FILE = 'post/files/';


    const TEXT_MESSAGE = 1;
    const FILE_MESSAGE = 2;
    const POST_MESSAGE = 3;
    const REPLY_MESSAGE = 4;
    const FORWORD_MESSAGE = 5;

    const MESSAGE_TYPE_ARRAY = [
        1 => "TEXT",
        2 => "FILE",
        3 => "POST",
        4 => "REPLY",
        5 => "FORWORD"
    ];
}
