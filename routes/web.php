<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDriverController;
use App\Http\Controllers\Admin\AdminParcelController;
use App\Http\Controllers\Admin\AdminTripsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminParcelProcessController;
use App\Http\Controllers\Admin\AdminPickupPointController;
use App\Http\Controllers\Admin\AdminTrailerController;


use App\Http\Middleware\AuthenticateAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route("admin.login");
});
Route::get('/privacy-policy', function () {
    return view('pages.privacy');
})->name("privacy-policy");
Route::get('/terms-condition', function () {
    return view('pages.terms');
})->name('admin.dashboard');

Route::group(['prefix' => 'admin'],function () {
    Route::get('/', [AdminAuthController::class, 'index'])->name('admin.login');
    Route::post('/authenticate', [AdminAuthController::class, 'authenticate'])->name('admin.authenticate');

    Route::get('/forget-password', [AdminAuthController::class, 'forgetPassword'])->name('admin.forget-password');
    Route::get('/enter-otp/{id}', [AdminAuthController::class, 'enterOtp'])->name('admin.enter-otp');
    Route::get('/resend-otp/{id}', [AdminAuthController::class, 'resendOtp'])->name('admin.resend-otp');
    Route::get('/reset-view/{id}', [AdminAuthController::class, 'resetPasswordView'])->name('admin.reset-password-view');

    Route::post('/send-otp', [AdminAuthController::class, 'sendOtp'])->name('admin.send-otp');
    Route::post('/verify-otp', [AdminAuthController::class, 'verifyOtp'])->name('admin.verify-otp');
    Route::post('/reset-password', [AdminAuthController::class, 'resetPassword'])->name('admin.reset-password');

    Route::middleware([AuthenticateAdmin::class])->group(function (){
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::group(['prefix' => 'profile'],function () {
            Route::get('/change', [AdminDashboardController::class, 'adminProfileChangeView'])->name('admin.profile-change-view');
            Route::post('/change', [AdminDashboardController::class, 'adminProfileChange'])->name('admin.profile-change');
            Route::get('/password-change', [AdminDashboardController::class, 'adminPasswordChangeView'])->name('admin.password-change-view');
            Route::post('/password-change', [AdminDashboardController::class, 'adminPasswordChange'])->name('admin.password-change');    
        });
        // User Managemnt Routes
        Route::group(['prefix' => 'users'],function () {
            Route::get('/', [AdminUserController::class, 'getUsers'])->name('admin.get-user-list');
            Route::get('/add', [AdminUserController::class, 'addUser'])->name('admin.add-user');
            Route::get('/view', [AdminUserController::class, 'viewUserDetails'])->name('admin.view-user');
            Route::get('/change-user-status',[AdminUserController::class, "changeUserStatus"])->name("admin.user-status-change");
        });
    
        Route::group(['prefix' => 'drivers'],function () {
            Route::get('/', [AdminDriverController::class, 'getDrivers'])->name('admin.get-driver-list');
            Route::get('/add', [AdminDriverController::class, 'addDrivers'])->name('admin.add-driver');
            Route::post('/add', [AdminDriverController::class, 'addDriversPost'])->name('admin.add-driver-post');
            Route::get('/edit', [AdminDriverController::class, 'editDrivers'])->name('admin.edit-driver');
            Route::post('/edit', [AdminDriverController::class, 'editDriversPost'])->name('admin.edit-driver-post');
            Route::get('/view', [AdminDriverController::class, 'viewDrivers'])->name('admin.view-driver');
            Route::get('/change-driver-status',[AdminDriverController::class, "changeDriverStatus"])->name("admin.driver-status-change");
            Route::post('/verify-driver', [AdminDriverController::class, 'verifyDriver'])->name('admin.driver-verify');
        });

        Route::group(['prefix' => 'parcels'],function () {
            Route::get('/', [AdminParcelController::class, 'getParcels'])->name('admin.get-parcel-list');
            Route::get('/add', [AdminParcelController::class, 'addParcels'])->name('admin.add-parcel');
            Route::get('/edit', [AdminParcelController::class, 'editParcels'])->name('admin.edit-parcel');
            Route::get('/view', [AdminParcelController::class, 'viewParcels'])->name('admin.view-parcel');
        });

        Route::group(['prefix' => 'trips'],function () {
            Route::get('/', [AdminTripsController::class, 'getTrips'])->name('admin.get-trip-list');
            Route::get('/add', [AdminTripsController::class, 'addTrips'])->name('admin.add-trip');
            Route::post('/add', [AdminTripsController::class, 'addTripsPost'])->name('admin.add-trip-post');
            Route::get('/edit', [AdminTripsController::class, 'editTrips'])->name('admin.edit-trip');
            Route::get('/view', [AdminTripsController::class, 'viewTrips'])->name('admin.view-trip');
        });

        Route::group(['prefix' => 'parcel-processing'],function () {
            Route::get('/', [AdminParcelProcessController::class, 'index'])->name('admin.get-parcel-process-index');
            Route::get('/search-parcel', [AdminParcelProcessController::class, 'searchParcel'])->name('admin.search-parcel');
            Route::get('/view', [AdminParcelProcessController::class, 'view'])->name('admin.view-parcel-process');
            Route::get('/add-parcel-to-trailer', [AdminParcelProcessController::class, 'addParcelToTrailer'])->name('admin.add-parcel-to-trailer');
        });

        Route::group(['prefix' => 'pickup'],function () {
            Route::get('/', [AdminPickupPointController::class, 'index'])->name('admin.get-pickup-points');
            Route::get('/add', [AdminPickupPointController::class, 'add'])->name('admin.add-pickup-point');
            Route::post('/add', [AdminPickupPointController::class, 'addpickupPoint'])->name('admin.add-pickup-point-post');
        });

        Route::group(['prefix' => 'trailer'],function () {
            Route::get('/', [AdminTrailerController::class, 'index'])->name('admin.get-trailer');
            Route::get('/get-details', [AdminTrailerController::class, 'getDeatails'])->name('admin.get-trailer-details');
            Route::get('/view', [AdminTrailerController::class, 'view'])->name('admin.view-trailer');
            Route::get('/lock-trailer', [AdminTrailerController::class, 'lockTrailer'])->name('admin.lock-trailer');
            Route::get('/remove-parcel', [AdminTrailerController::class, 'removeParcel'])->name('admin.remove-parcel-from-trailer');
        });
    
        //Admin Logout 
        Route::get('/logout', [AdminDashboardController::class, 'adminLogout'])->name('admin.logout');
    });

});