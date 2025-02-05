<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Mail\EmailService;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user/get-my-profile",
     *     tags={"User Profile"},
     *     summary="Get User Profile",
     *     description="Retrieve the authenticated user's profile information.",
     *     operationId="getMyProfile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User Profile Retrieved Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                     @OA\Property(property="phone_number", type="string", example="1234567890"),
     *                     @OA\Property(property="profile_image", type="string", nullable=true, example="http://example.com/profile.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="User Profile Get Successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function getMyProfile(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $userArray = [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "is_user" => 1, 
                "phone_code" => (string) $user->phone_code,
                "phone_number" => $user->phone_number,
                "profile_image" => $user->profile_image ? asset($user->profile_image) : null,
                "is_email_verified" => $user->is_email_verified,
                "is_password_set" => $user->is_password_set 
            ];
            $response = [
                "user" => $userArray
            ];
            return ApiResponse::successResponse($response, "User Profile Get Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/update-profile",
     *     tags={"User Profile"},
     *     summary="Update User Profile",
     *     description="Allows the authenticated user to update their profile details such as name, email, phone number, and profile image.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="User's full name",
     *                     example="John Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     description="User's email address",
     *                     example="johndoe@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="phone_number",
     *                     type="string",
     *                     description="User's phone number",
     *                     example="1234567890"
     *                 ),
     *                 @OA\Property(
     *                     property="profile_image",
     *                     type="string",
     *                     format="binary",
     *                     description="User's profile image file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile Updated Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User Profile Updated Successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                     @OA\Property(property="phone_number", type="string", example="1234567890"),
     *                     @OA\Property(property="profile_image", type="string", example="https://yourdomain.com/uploads/profile_images/john_doe.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error or Email Already Taken",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Email is already taken.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'phone_number' => 'nullable|numeric|digits_between:8,15',
                "phone_code" => "nullable|numeric|digits_between:1,5",
                'name' => "nullable|string|min:3|max:55|regex:/^[a-zA-Z\s]+$/",
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $isEmailVerified = $user->is_email_verified;
            if (isset($request->name) && !empty($request->name)) {
                $user->name = $request->name;
            }
            if (isset($request->email) && !empty($request->email)) {
                $existingCheck = User::where("email", $request->email)->first();
                if ($existingCheck && $user->id != $existingCheck->id) {
                    return ApiResponse::successResponse(null, "Email is already taken.", ProjectConstants::BAD_REQUEST);
                }
                if ($user->email != $request->email) {
                    $data = [
                        "email" => $request->email
                    ];
                    $isEmailVerified = ProjectConstants::EMAIL_NOT_VERIFIED;
                    $user->old_data = json_encode($data);
                    $emailOtp = $user->genrateEmailOtp();
                    
                    $subject = "Your One-Time Password (OTP) for email verification ";
                    $name = $user->name;
                    Mail::to($request->email)->send(new EmailService($subject, $name,$emailOtp));
                }
            }
            if (isset($request->phone_number) && !empty($request->phone_number)) {
                $user->phone_code = $request->phone_code;
                $user->phone_number = $request->phone_number;
            }
            if ($request->has('profile_image') && !empty('profile_image')) {
                $uploadedFile = $request->file('profile_image');
                $user->profile_image = AwsHelper::uploadFile($uploadedFile, ProjectConstants::USER_PROFILE);
            }
            $user->save();
            $userArray = [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "phone_code" => (string) $user->phone_code,
                "phone_number" => $user->phone_number,
                "profile_image" => $user->profile_image ? asset($user->profile_image) : null,
                "is_email_verified" => $isEmailVerified,
                "is_password_set" => $user->is_password_set 
            ];
            $response = [
                "user" => $userArray
            ];
            $message = "User Profile Updated Successfully.";
            if($isEmailVerified == 0) {
                $message = "You need to verify your email address.";
            }

            return ApiResponse::successResponse($response, $message, ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * Update User's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/v1/user/change-password",
     *     summary="Update User's password",
     *     tags={"User Profile"},
     *     operationId="updatePassword",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password", "new_password", "confirm_password"},
     *             @OA\Property(property="old_password", type="string", description="Old password"),
     *             @OA\Property(property="new_password", type="string", description="New password"),
     *             @OA\Property(property="confirm_password", type="string", description="Confirm password, must match new password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Old password does not match"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"new_password": {"The new password field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = Auth::guard('user')->user();
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/',
                'confirm_password' => 'required|same:new_password',
            ],[
                "new_password.regex" => "Password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character (e.g., @$!%*#?&), and must only include valid characters."
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            if (Hash::check($request->old_password, $user->password)) {
                $user->password = $request->new_password;
                $user->is_password_set = 1;
                $user->save();
                return ApiResponse::successResponse([], "Password changed sucessfully.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse([], "Old Password not matched.", ProjectConstants::BAD_REQUEST);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([], "Server error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/user/log-out",
     *      tags={"User Profile"},
     *      summary="/logout",
     *      description="/logout.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              example={ "message": "Account logout Successfully."}
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              example={"error": "Token Expired or Not Existed"}
     *          )
     *      )
     * )
     */
    public function userLogOut(Request $request)
    {
        try {
            Auth::guard('user')->user()->tokens()->delete();
            return ApiResponse::successResponse([], "User Logged Out Successfully", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([], "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * Delete user account.
     *
     * @OA\Get(
     *     path="/api/v1/user/delete-my-account",
     *     operationId="deleteMyAccount",
     *     tags={"User Profile"},
     *     summary="Delete user account",
     *     description="Deletes the authenticated user's account along with associated user addresses.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Account deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"message": "Account Deleted Successfully"}
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server Error")
     * )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMyAccount(Request $request)
    {
        try {
            $user = Auth::guard('user')->user();
            // UserAddress::where('user_id', $user->id)->delete();
            // User::where('id', $user->id)->delete();
            return ApiResponse::successResponse([], "Account Deleted Successfully", ProjectConstants::SUCCESS);
        } catch (Exception $e) {
            Log::error($e);
            return ApiResponse::errorResponse([], "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     *  @OA\Get(
     *     path="/api/v1/user/get-faqs",
     *     tags={"Home Page"},
     *     summary="Get FAQs",
     *     description="Retrieve a list of frequently asked questions.",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="faqs",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="question", type="string"),
     *                     @OA\Property(property="answer", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */
    public function getFaqs(Request $request)
    {
        try {
            $response = [
                "faqs" => [
                    [
                        "id" => 1,
                        "question" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
                        "answer" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
                    ],
                    [
                        "id" => 2,
                        "question" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
                        "answer" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
                    ],
                    [
                        "id" => 3,
                        "question" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
                        "answer" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
                    ],
                    [
                        "id" => 4,
                        "question" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
                        "answer" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
                    ]
                ]
            ];
            return ApiResponse::successResponse($response, "Faq Data Got Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/contact-us",
     *     tags={"Contact Us"},
     *     summary="Submit a Contact Us Form",
     *     description="Submit a form with the user's name, email, and description.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="description", type="string", example="I need help with...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Form submitted successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Your message has been sent successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function contactUs(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'description' => 'required|string|max:1255',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            // Mail::to($request->email)->send(new EmailService());
            return ApiResponse::successResponse(null, "Your message has been sent successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }
}
