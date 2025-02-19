<?php
use App\Http\Controllers\User\UserAuthController;
use App\Http\Controllers\User\UserBlogController;
use App\Http\Controllers\User\UserChatController;
use App\Http\Controllers\User\UserFolderDocumnetController;
use App\Http\Controllers\User\UserFolderManagement;
use App\Http\Controllers\User\UserPostController;
use App\Http\Controllers\User\UserProfileController;
use App\Http\Middleware\AuthenticateUser;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminCategoryController;


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
        
        Route::post("/social/apple-login", [UserAuthController::class, "socialAuthentication"]);            
        
        Route::middleware([AuthenticateUser::class])->group(function () {
            Route::get("/log-out", [UserProfileController::class, "userLogOut"]);
            Route::get("/delete-my-account", [UserProfileController::class, "deleteMyAccount"]);
            Route::get("/get-my-profile", [UserProfileController::class, "getMyProfile"]);
            Route::post("/update-profile", [UserProfileController::class, "updateProfile"]);  
            Route::post("/change-password", [UserProfileController::class, "updatePassword"]);  
            
            //Home api
            Route::post("/create-new-folder", [UserFolderDocumnetController::class, "createNewFolder"]);
            Route::post("/edit-folder", [UserFolderDocumnetController::class, "editFolder"]);

            Route::get("/get-all-folders", [UserFolderDocumnetController::class, "getAllFolders"]);
            Route::get("/delete-folder", [UserFolderDocumnetController::class, "deleteFolder"]);
            Route::post("/add-files-to-folder", [UserFolderDocumnetController::class, "addDocumentsToFolder"]);
            Route::get("/get-folder-by-id", [UserFolderDocumnetController::class, "getFolderById"]);
            Route::get("/delete-file", [UserFolderDocumnetController::class, "deleteFile"]);

            // Blog apis
            Route::get("/get-all-blogs", [UserBlogController::class, "getAllBlogs"]);
            
            Route::group(['prefix' => 'categorys'],function () {
                Route::get('/', [AdminCategoryController::class, 'getAllCategorysApi']);
            });    
            // Post Apis
            Route::get("/get-issues-list", [UserPostController::class, "getReportIssuesList"]);
            Route::post("/create-post", [UserPostController::class, "createPost"]);
            Route::get("/get-community-list", [UserPostController::class, "getCommunityList"]);
            Route::get("/get-city-list", [UserPostController::class, "getCity"]);
            Route::get("/get-community-post", [UserPostController::class, "getCommunityPost"]);
            Route::get("/post/details", [UserPostController::class, "getPostById"]);
            Route::get("/post/delete", [UserPostController::class, "deletePost"]);
            Route::get("/post/like", [UserPostController::class, "likePost"]);
            Route::post("/post/comment", [UserPostController::class, "commentPost"]);
            Route::get("/post/comment", [UserPostController::class, "getPostAllComment"]);
            Route::get("/post/comment/like", [UserPostController::class, "likeComment"]);
            Route::post("/post/report", [UserPostController::class, "reportPost"]);
            Route::get("/reasons", [UserPostController::class, "getReasons"]);

            Route::group(['prefix' => 'socket'],function () { 
                Route::post("/register", [UserChatController::class, "registerSocket"]);
                Route::post("/send-message", [UserChatController::class, "sendMessage"]);
                Route::get("/get-chat-history", [UserChatController::class, "getChatHistory"]);

                Route::post('/react-to-chat-request', [UserChatController::class, 'reactToChatRequest']);
                Route::get("/get-chat-requests", [UserChatController::class, "getChatRequests"]);
                Route::get("/get-chat-list", [UserChatController::class, "getChatLists"]);


            });
        });
    });
});

?>