<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBlogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDriverController;
use App\Http\Controllers\Admin\AdminParcelController;
use App\Http\Controllers\Admin\AdminTripsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminParcelProcessController;
use App\Http\Controllers\Admin\AdminPickupPointController;
use App\Http\Controllers\Admin\AdminTrailerController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\User\UserAuthController;
use App\Http\Middleware\AuthenticateAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view("welcome");
    return redirect()->route("admin.login");
})->name("web.landing");
Route::get('/privacy-policy', function () {
    return view('pages.privacy');
})->name("privacy-policy");
Route::get('/terms-condition', function () {
    return view('pages.terms');
})->name('admin.terms-condition');

Route::get('/contact-us', function () {
    return view('pages.contact-us');
})->name('admin.contact-us');
Route::post('contact-us', [AdminAuthController::class, "contactUs"])->name("contact-us");

Route::get('/test', [AdminAuthController::class, 'test']);

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

    Route::group(['prefix' => 'notified'],function () {
        Route::post('get-notified', [AdminUserController::class, "getNotified"])->name("admin.get-notified");
        Route::get('/', [AdminUserController::class, 'getNotifiedEmails'])->name('admin.get-notified-emails');
    });
    Route::middleware([AuthenticateAdmin::class])->group(function (){
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::group(['prefix' => 'profile'],function () {
            Route::get('/change', [AdminDashboardController::class, 'adminProfileChangeView'])->name('admin.profile-change-view');
            Route::post('/change', [AdminDashboardController::class, 'adminProfileChange'])->name('admin.profile-change');
            Route::get('/password-change', [AdminDashboardController::class, 'adminPasswordChangeView'])->name('admin.password-change-view');
            Route::post('/password-change', [AdminDashboardController::class, 'adminPasswordChange'])->name('admin.password-change');    
        });
        Route::group(['prefix' => 'users'],function () {
            Route::get('/', [AdminUserController::class, 'getUsers'])->name('admin.get-user-list');
            Route::get('/add', [AdminUserController::class, 'addUser'])->name('admin.add-user');
            Route::get('/view', [AdminUserController::class, 'viewUserDetails'])->name('admin.view-user');
            Route::get('/change-user-status',[AdminUserController::class, "changeUserStatus"])->name("admin.user-status-change");
        });

        Route::group(['prefix' => 'blogs'],function () {
            Route::get('/', [AdminBlogController::class, 'getBlogs'])->name('admin.get-blog-list');
            Route::get('/add', [AdminBlogController::class, 'addBlog'])->name('admin.add-blog');
            Route::post('/add', [AdminBlogController::class, 'addBlogPost'])->name('admin.add-blog-post');
            Route::post('/upload-file', [AdminBlogController::class, 'uploadFile'])->name('admin.upload-file');            
            Route::get('/view', [AdminBlogController::class, 'viewBlogDetails'])->name('admin.view-blog');
            Route::get('/edit', [AdminBlogController::class, 'editBlog'])->name('admin.edit-blog');
            Route::get('/change-blog-status',[AdminBlogController::class, "changeBlogStatus"])->name("admin.blog-status-change");
            Route::get('/delete',[AdminBlogController::class, "deleteBlog"])->name("admin.delete-blog");
        });

        Route::group(['prefix' => 'categorys'],function () {
            Route::get('/', [AdminCategoryController::class, 'getAllCategorys'])->name('admin.get-all-category');
            Route::get('/add', [AdminCategoryController::class, 'addCategory'])->name('admin.add-category');
            Route::post('/add', [AdminCategoryController::class, 'addCategoryPost'])->name('admin.add-category-post');        
            Route::get('/view', [AdminCategoryController::class, 'viewCategoryDetails'])->name('admin.view-category');
            Route::get('/edit', [AdminCategoryController::class, 'editCategory'])->name('admin.edit-category');
            Route::get('/change-blog-status',[AdminBlogController::class, "changeBlogStatus"])->name("admin.blog-status-change");
            Route::get('/delete',[AdminBlogController::class, "deleteBlog"])->name("admin.delete-blog");
        });
    
        //Admin Logout 
        Route::get('/logout', [AdminDashboardController::class, 'adminLogout'])->name('admin.logout');
    });

});