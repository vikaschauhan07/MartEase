<?php

use App\Http\Controllers\Driver\DriverAuthController;
use App\Http\Controllers\Driver\DriverDocumentController;
use App\Http\Controllers\Driver\DriverProfileController;
use App\Http\Controllers\Driver\DriverTripController;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\UserPackageController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Middleware\AuthenticateDriver;
use App\Http\Middleware\AuthenticateUser;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'],function () {

    Route::group(['prefix' => 'user'],function () {
        Route::post("/authenticate",[UserAuthController::class, "authenticate"]);
        Route::post("/register",[UserAuthController::class, "register"]);
        Route::post("/verify-phone-otp",[UserAuthController::class, "verifyPhoneOtp"]);
        Route::post("/resend-phone-otp",[UserAuthController::class, "resendPhoneOtp"]);
        Route::post("/verify-email-otp",[UserAuthController::class, "verifyEmailOtp"]);
        Route::post("/resend-email-otp",[UserAuthController::class, "resendEmailOtp"]);
        Route::post("/forget-password", [UserAuthController::class, "forgetPassword"]);
        Route::post("/resend-otp", [UserAuthController::class, "resendOtp"]);
        Route::post("/verify-otp", [UserAuthController::class, "verifyOtp"]);
        Route::post("/reset-password", [UserAuthController::class, "resetPassword"]);  
        //Faq and Mail Ids
        Route::get("/get-faqs", [UserProfileController::class, "getFaqs"]);
        Route::post("/contact-us", [UserProfileController::class, "contactUs"]);
        
        //Package Add Apis 
        Route::get("/home", [UserProfileController::class, "home"]);
        Route::post("/create-package", [UserPackageController::class, "createPackage"]);
        Route::post("/add-sender-details", [UserPackageController::class, "addSenderDetails"]);
        Route::post("/add-reciver-details", [UserPackageController::class, "addReciverDetails"]);
        Route::get("/get-city-and-hitchmail-details", [UserPackageController::class, "getCityAndHitchmailDetails"]);
        Route::get("/get-pickup-points", [UserPackageController::class, "getPickupPoints"]);
        Route::post("/add-payment-to-parcel", [UserPackageController::class, "addPayment"]);
        Route::post("/add-images-to-parcel", [UserPackageController::class, "addImagesToParcel"]);
        Route::post("/complete-parcel", [UserPackageController::class, "completePackage"]);
        Route::get("/track-package", [UserPackageController::class, "trackPackage"]);
        Route::get("/get-package-details", [UserPackageController::class, "getPackageDtails"]);
        
        Route::middleware([AuthenticateUser::class])->group(function () {
            Route::get("/log-out", [UserProfileController::class, "userLogOut"]);
            Route::get("/delete-my-account", [UserProfileController::class, "deleteMyAccount"]);
            Route::get("/get-my-profile", [UserProfileController::class, "getMyProfile"]);
            Route::post("/update-profile", [UserProfileController::class, "updateProfile"]);  
            Route::post("/change-password", [UserProfileController::class, "updatePassword"]);  
             
            //Home api
            Route::get("/get-parcel-history",[UserPackageController::class, "getPacakageHistory"]);
        });
    });

    Route::group(['prefix' => 'driver'],function () {
        Route::post("/authenticate",[DriverAuthController::class, "authenticate"]);
        Route::post("/register",[DriverAuthController::class, "register"]);
        Route::post("/verify-phone-otp",[DriverAuthController::class, "verifyPhoneOtp"]);
        Route::post("/resend-phone-otp",[DriverAuthController::class, "resendPhoneOtp"]);
        Route::post("/verify-email-otp",[DriverAuthController::class, "verifyEmailOtp"]);
        Route::post("/resend-email-otp",[DriverAuthController::class, "resendEmailOtp"]);
        Route::post("/forget-password", [DriverAuthController::class, "forgetPassword"]);
        Route::post("/resend-otp", [DriverAuthController::class, "resendOtp"]);
        Route::post("/verify-otp", [DriverAuthController::class, "verifyOtp"]);
        Route::post("/reset-password", [DriverAuthController::class, "resetPassword"]);  
        Route::post("/add-driver-details",[DriverDocumentController::class,"addDriverDetails"]);
        Route::middleware([AuthenticateDriver::class])->group(function () {
            Route::get("/get-my-profile", [DriverProfileController::class, "getMyProfile"]);
            Route::post("/update-profile", [DriverProfileController::class, "updateProfile"]);  
            Route::post("/change-password", [DriverProfileController::class, "updatePassword"]);  
            Route::get("/delete-my-account", [DriverProfileController::class, "deleteMyAccount"]);
            Route::get("/log-out", [DriverProfileController::class, "userLogOut"]);

            Route::get("/get-posted-trips", [DriverTripController::class, "getPostedTrips"]);
            Route::get("/get-trip", [DriverTripController::class, "getTripDetails"]);

        });
    });
});

?>