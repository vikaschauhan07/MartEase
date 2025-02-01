<?php

namespace App\Http\Controllers\Driver;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Mail\EmailService;
use App\Models\Drivers;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class DriverProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/driver/get-my-profile",
     *     tags={"Driver Profile"},
     *     summary="Get Driver Profile",
     *     description="Retrieve the authenticated driver's profile information.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="driver Profile Retrieved Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="driver", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *                     @OA\Property(property="phone_number", type="string", example="1234567890"),
     *                     @OA\Property(property="profile_image", type="string", nullable=true, example="http://example.com/profile.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Driver Profile Get Successfully.")
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
            $driver = Auth::guard("drivers")->user();
            $driverArray = [
                "id" => $driver->id,
                "name" => $driver->name,
                "email" => $driver->email,
                "phone_number" => $driver->phone_number,
                "profile_image" => $driver->profile_image ? asset($driver->profile_image) : null,
                "is_phone_verified" => $driver->is_phone_verified,
                "is_email_verified" => $driver->is_email_verified
            ];
            $response = [
                "driver" => $driverArray
            ];
            return ApiResponse::successResponse($response, "Driver Profile Get Successfully.", ProjectConstants::SUCCESS);
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
     *     path="/api/v1/driver/change-password",
     *     summary="Update Driver's password",
     *     tags={"Driver Profile"},
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
            $driver = Auth::guard('drivers')->user();
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/',
                'confirm_password' => 'required|same:new_password',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            if (Hash::check($request->old_password, $driver->password)) {
                $driver->password = $request->new_password;
                $driver->save();
                return ApiResponse::successResponse([], "Password changed sucessfully.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse([], "Old Password not matched.", ProjectConstants::BAD_REQUEST);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([], "Server error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/driver/update-profile",
     *     tags={"Driver Profile"},
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
                'name' => "nullable|string|min:3|max:55|regex:/^[a-zA-Z\s]+$/",
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:4048',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $driver = Auth::guard("drivers")->user();
            $isEmailVerified = $driver->is_email_verified;
            $isNumberVerified = $driver->is_phone_verified;
            if (isset($request->name) && !empty($request->name)) {
                $driver->name = $request->name;
            }
            if (isset($request->email) && !empty($request->email)) {
                $existingCheck = Drivers::where("email", $request->email)->first();
                if ($existingCheck && $driver->id != $existingCheck->id) {
                    return ApiResponse::successResponse(null, "Email is already taken.", ProjectConstants::BAD_REQUEST);
                }
                if ($driver->email != $request->email) {
                    $data = [
                        "email" => $request->email,
                        "phone_number" => null
                    ];
                    $isEmailVerified = ProjectConstants::EMAIL_NOT_VERIFIED;
                    $driver->old_data = json_encode($data);
                    $emailOtp = $driver->genrateEmailOtp();
                    $subject = "Verification otp mail";
                    Mail::to($request->email)->send(new EmailService($subject, $emailOtp));
                }
            }
            if (isset($request->phone_number) && !empty($request->phone_number)) {
                $existingCheck = Drivers::where("phone_number", $request->phone_number)->first();
                if ($existingCheck && $driver->id != $existingCheck->id) {
                    return ApiResponse::successResponse(null, "Phone Number is already taken.", ProjectConstants::BAD_REQUEST);
                }
                if ($driver->phone_number != $request->phone_number) {
                    $data = [
                        "phone_number" => $request->phone_number,
                        "email" => null
                    ];
                    if ($driver->old_data) {
                        $data = json_decode($driver->old_data, true);
                        $data = [
                            "phone_number" => $request->phone_number,
                            "email" => $data['email'] ?? null
                        ];
                    }
                    $driver->old_data = json_encode($data);
                    $isNumberVerified = ProjectConstants::PHONE_NOT_VERIFIED;
                    $phoneOtp = $driver->genratePhoneOtp();
                }
            }
            if ($request->has('profile_image') && !empty('profile_image')) {
                $uploadedFile = $request->file('profile_image');
                $driver->profile_image = AwsHelper::uploadFile($uploadedFile, ProjectConstants::USER_PROFILE);
            }
            $driver->save();
            $driverArray = [
                "id" => $driver->id,
                "name" => $driver->name,
                "email" => $driver->email,
                "phone_number" => $driver->phone_number,
                "profile_image" => $driver->profile_image ? asset($driver->profile_image) : null,
                "is_phone_verified" => $isNumberVerified,
                "is_email_verified" => $isEmailVerified
            ];
            $response = [
                "driver" => $driverArray
            ];
            return ApiResponse::successResponse($response, "Driver Profile Updated Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/driver/log-out",
     *      tags={"Driver Profile"},
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
            Auth::guard('drivers')->user()->tokens()->delete();
            return ApiResponse::successResponse([], "Driver Logged Out Successfully", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([], "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * Delete user account.
     *
     * @OA\Get(
     *     path="/api/v1/driver/delete-my-account",
     *     tags={"Driver Profile"},
     *     summary="Delete driver account",
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
            $driver = Auth::guard('drivers')->user();
            // UserAddress::where('user_id', $user->id)->delete();
            // User::where('id', $user->id)->delete();
            return ApiResponse::successResponse([], "Account Deleted Successfully", ProjectConstants::SUCCESS);
        } catch (Exception $e) {
            Log::error($e);
            return ApiResponse::errorResponse([], "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }
}
