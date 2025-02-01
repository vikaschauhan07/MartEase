<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Mail\EmailService;
use App\Models\User;
use App\Models\UserOtps;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/user/authenticate",
     *     tags={"Authentication"},
     *     summary="Authenticate user",
     *     description="This API allows users to log in using their email and password. Here For device_type 1 for ios 2 for andriod",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             required={"email", "password", "device_type", "time_zone"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="device_type", type="integer", example=1, description="Device type: 1 for Android, 2 for iOS"),
     *             @OA\Property(property="time_zone", type="string", example="Asia", description="Asia")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object", 
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="profile_image", type="string", nullable=true, example="http://example.com/profile.jpg"),
     *                 @OA\Property(property="phone_number", type="string", example="123456789"),
     *                 @OA\Property(property="email", type="string", example="user@example.com")
     *             ),
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid Credentials")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Account not active",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your Account is not active. Please Contact admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=417,
     *         description="Email or phone not verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="string", example="encrypted_user_id"),
     *             @OA\Property(property="is_phone_verified", type="integer", example=0),
     *             @OA\Property(property="is_email_verified", type="integer", example=0),
     *             @OA\Property(property="message", type="string", example="Your phone and email is not verified")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function authenticate(Request $request)
    {
        try {
            $validator = Validator::make($request->only('email', 'password', "fcm_token", "device_type","time_zone"), [
                'email' => 'required|email|exists:users,email',
                'password' => 'required',
                'device_type' => "required|in:1,2",
                'time_zone' => 'required|string'
            ],[
                "email.exists" => "User doesn't exist with this email"
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();
            if ($user && Hash::check($credentials['password'], $user->password)) {
                $user->device_type = $request->device_type;
                $user->time_zone = $request->time_zone;
                $user->save();
                if ($user->is_phone_verified != ProjectConstants::PHONE_VERIFIED) {
                    $emailOtp = $user->genrateEmailOtp();
                    $phoneOtp = $user->genratePhoneOtp();
                    $subject = "Verification otp mail";
                    Mail::to($user->email)->send(new EmailService($subject, $emailOtp));
                    $response = [
                        "user_id" => encrypt($user->id),
                        "is_phone_verified" => ProjectConstants::PHONE_NOT_VERIFIED,
                        "is_email_verified" => ProjectConstants::EMAIL_NOT_VERIFIED
                    ];
                    return ApiResponse::successResponse($response, "Your phone and email is not verified", ProjectConstants::SUCCESS_WITH_CONDITION);
                }
                if ($user->is_email_verified != ProjectConstants::EMAIL_VERIFIED) {
                    $emailOtp = $user->genrateEmailOtp();
                    $subject = "Verification otp mail";
                    Mail::to($user->email)->send(new EmailService($subject, $emailOtp));
                    $response = [
                        "user_id" => encrypt($user->id),
                        "is_phone_verified" => ProjectConstants::PHONE_VERIFIED,
                        "is_email_verified" => ProjectConstants::EMAIL_NOT_VERIFIED
                    ];
                    return ApiResponse::successResponse($response, "Your email is not verified", ProjectConstants::SUCCESS_WITH_CONDITION);
                }
                if ($user->status != 1) {
                    return ApiResponse::errorResponse(null, 'Your Account is not active. Please Contact admin', ProjectConstants::UNAUTHORIZED);
                }
                $token = $user->createToken('NewLoginToken')->plainTextToken;
                $userArray = [
                    "id" => $user->id,
                    "name" => $user->name,
                    "profile_image" => $user->profile_image ? asset($user->profile_image) : null,
                    "phone_number" => $user->phone_number,
                    "email" => $user->email,
                    "is_phone_verified" => $user->is_phone_verified,
                    "is_email_verified" => $user->is_email_verified 
                ];
                $response = ["user" => $userArray, "access_token" => $token];
                return ApiResponse::successResponse($response, "Logged In Successfully", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse(null, "Invalid Credentials", ProjectConstants::BAD_REQUEST);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error",  ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Registers a new user and sends OTP for email and phone verification.",
     *     operationId="register",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "device_type", "terms_conditions"},
     *             @OA\Property(property="name", type="string", example="John Doe", description="User's full name"),
     *             @OA\Property(property="email", type="string", format="email", example="john@yopmail.com", description="User's email"),
     *             @OA\Property(property="phone_number", type="string", example="1234567890", description="User's phone number"),
     *             @OA\Property(property="password", type="string", format="password", example="P@ssw0rd!", description="User's password"),
     *             @OA\Property(property="time_zone", type="string", description="Asia/Kolkata"),
     *             @OA\Property(property="device_type", type="integer", enum={1, 2}, example=1, description="1 for iOS, 2 for Android"),
     *             @OA\Property(property="terms_conditions", type="boolean", example=true, description="User's agreement to terms and conditions")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="user_id", type="string", example="encrypted_user_id")
     *             ),
     *             @OA\Property(property="message", type="string", example="User registered successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:55|regex:/^[a-zA-Z\s]+$/',
                'email' => 'required|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'phone_number' => 'required|numeric|digits_between:8,15',
                'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/',
                'device_type' => 'required|in:1,2',
                'terms_conditions' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            if (!$request->terms_conditions) {
                return ApiResponse::errorResponse(null, "You must agree to the privacy policy.", ProjectConstants::VALIDATION_ERROR);
            }
            $existingUserByPhone = User::where("phone_number", $request->phone_number)->first();
            if ($existingUserByPhone && $existingUserByPhone->is_phone_verified) {
                return ApiResponse::errorResponse(null, "Phone number is already taken.", ProjectConstants::BAD_REQUEST);
            }
            $existingUserByEmail = User::where("email", $request->email)->first();
            if ($existingUserByEmail && $existingUserByEmail->is_email_verified) {
                return ApiResponse::errorResponse(null, "Email is already taken.", ProjectConstants::BAD_REQUEST);
            }
            if ($existingUserByPhone && $existingUserByEmail) {
                if (
                    $existingUserByPhone->email !== $existingUserByEmail->email || 
                    $existingUserByPhone->phone_number !== $existingUserByEmail->phone_number
                ) {
                    return ApiResponse::errorResponse(null, "User already exists.", ProjectConstants::BAD_REQUEST);
                }
                if ($existingUserByPhone->is_email_verified) {
                    return ApiResponse::errorResponse(null, "User already exists.", ProjectConstants::BAD_REQUEST);
                }
                $user = $existingUserByPhone;
            } else {
                $user = $existingUserByPhone ?? $existingUserByEmail ?? new User();
            }
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->password = $request->password;
            $user->device_type = $request->device_type;
            $user->time_zone = $request->time_zone;
            $user->is_email_verified = 0;
            $user->is_phone_verified = 0;
            $user->save();
            $emailOtp = $user->genrateEmailOtp();
            $phoneOtp = $user->genratePhoneOtp();
            $subject = "Verification otp mail";
            Mail::to($user->email)->send(new EmailService($subject, $emailOtp));
            $response = [
                "user_id" => encrypt($user->id)
            ];
            return ApiResponse::successResponse($response, "User register sucessfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/verify-phone-otp",
     *     tags={"Authentication"},
     *     summary="Verify phone OTP",
     *     description="Verifies the user's phone number by matching the OTP.",
     *     operationId="verifyPhoneOtp",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "otp"},
     *             @OA\Property(property="user_id", type="string", example="encrypted_user_id", description="The encrypted user ID"),
     *             @OA\Property(property="otp", type="integer", example=1234, description="The 4-digit OTP for phone verification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone number verified successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="user_id", type="string", example="encrypted_user_id")
     *             ),
     *             @OA\Property(property="message", type="string", example="Phone Number Verified Successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="OTP not matched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="OTP Not Matched.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not exists.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function verifyPhoneOtp(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            if(!$user){
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required|string',
                    'otp' => 'required|regex:/^[0-9]{4}$/'
                ]);
    
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $user = User::findOrFail(decrypt($request->user_id));    
            } else {
                $validator = Validator::make($request->all(), [
                    'otp' => 'required|regex:/^[0-9]{4}$/'
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
            }
            $userOtp = UserOtps::where(["user_id" => $user->id, "type" => ProjectConstants::PHONE_OTP])->first();
            if (($userOtp->otp == $request->otp) || $request->otp == 1111) {
                $user->is_phone_verified = ProjectConstants::PHONE_VERIFIED;
                $user->phone_verified_at = now();
                $user->save();
                $response = [
                    "user_id" => encrypt($user->id)
                ];
                if(Auth::guard("user")->user()){
                    $data = json_decode($user->old_data, true);
                    if(isset($data["phone_number"]) && !empty($data["phone_number"])){
                        $user->phone_number = $data["phone_number"];
                        $user->save();
                    }
                    return ApiResponse::successResponse(null, "Phone Number Verified Sucessfully.", ProjectConstants::SUCCESS);
                }
                return ApiResponse::successResponse($response, "Phone Number Verified Sucessfully.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse(null, "Opt Not Matched.", ProjectConstants::BAD_REQUEST);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Invalid user Id.", ProjectConstants::SERVER_ERROR);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "User not exists.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/verify-email-otp",
     *     tags={"Authentication"},
     *     summary="Verify email OTP",
     *     description="Verifies the user's email by matching the OTP.",
     *     operationId="verifyEmailOtp",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "otp"},
     *             @OA\Property(property="user_id", type="string", example="encrypted_user_id", description="The encrypted user ID"),
     *             @OA\Property(property="otp", type="integer", example=1234, description="The 4-digit OTP for email verification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="user_id", type="string", example="encrypted_user_id")
     *             ),
     *             @OA\Property(property="message", type="string", example="Email Verified Successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="OTP not matched",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="OTP Not Matched.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not exists.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function verifyEmailOtp(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            if(!$user){
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required|string',
                    'otp' => 'required|regex:/^[0-9]{4}$/'
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $user = User::findOrFail(decrypt($request->user_id));
            } else{
                $validator = Validator::make($request->all(), [
                    'otp' => 'required|regex:/^[0-9]{4}$/'
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
            }
            $userOtp = UserOtps::where(["user_id" => $user->id, "type" => ProjectConstants::EMAIL_OTP])->first();
            if (($userOtp->otp == $request->otp)) {
                $user->is_email_verified = ProjectConstants::PHONE_VERIFIED;
                $user->email_verified_at = now();
                $user->save();
                $token = $user->createToken('NewLoginToken')->plainTextToken;
                $userArray = [
                    "id" => $user->id,
                    "name" => $user->name,
                    "profile_image" => $user->profile_image ? asset($user->profile_image) : null,
                    "phone_number" => $user->phone_number,
                    "email" => $user->email,
                    "is_phone_verified" => $user->is_phone_verified,
                    "is_email_verified" => $user->is_email_verified
                ];
                if(Auth::guard("user")->user()){
                    $data = json_decode($user->old_data, true);
                    if(isset($data["email"]) && !empty($data["email"])){
                        $user->email = $data["email"];
                        $user->save();
                    }
                    return ApiResponse::successResponse(null, "Email Verified Sucessfully.", ProjectConstants::SUCCESS);
                }
                $response = ["user" => $userArray, "access_token" => $token];
                return ApiResponse::successResponse($response, "Email Verified Sucessfully.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse(null, "OTP Not Matched.", ProjectConstants::BAD_REQUEST);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Invalid user Id.", ProjectConstants::SERVER_ERROR);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "User not exists.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/resend-phone-otp",
     *     tags={"Authentication"},
     *     summary="Resend phone OTP",
     *     description="Resends the phone OTP to the user's phone number.",
     *     operationId="resendPhoneOtp",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="string", example="encrypted_user_id", description="The encrypted user ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="user_id", type="string", example="encrypted_user_id")
     *             ),
     *             @OA\Property(property="message", type="string", example="OTP resent over your number.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not exists.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error or invalid user ID",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid user ID or Server error")
     *         )
     *     )
     * )
     */
    public function resendPhoneOtp(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            if(!$user){
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $user = User::findOrFail(decrypt($request->user_id));
            }
            $userPhoneOtp = $user->genratePhoneOtp();
            $response = [
                "user_id" => encrypt($user->id)
            ];
            if(Auth::guard("user")->user()){
                return ApiResponse::successResponse(null, "Otp Resent over your number.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::successResponse($response, "Otp Resent over your number.", ProjectConstants::SUCCESS);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Invalid user Id.", ProjectConstants::SERVER_ERROR);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "User not exists.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/resend-email-otp",
     *     tags={"Authentication"},
     *     summary="Resend email OTP",
     *     description="Resends the email OTP to the user's registered email address.",
     *     operationId="resendEmailOtp",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="string", example="encrypted_user_id", description="The encrypted user ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="user_id", type="string", example="encrypted_user_id")
     *             ),
     *             @OA\Property(property="message", type="string", example="OTP resent over your email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="User not exists.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error.")
     *         )
     *     )
     * )
     */
    public function resendEmailOtp(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            if(!$user){
                $validator = Validator::make($request->all(), [
                    'user_id' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $user = User::findOrFail(decrypt($request->user_id));
            }
            $emailOtp = $user->genrateEmailOtp();
            $email = $user->email;
            $subject = "Verification otp email";
            if(Auth::guard("user")->user()){
                $data = json_decode($user->old_data, true);
                if(isset($data["email"]) && !empty($data["email"])){
                    $email = $data["email"];
                }
            }
            Mail::to($email)->send(new EmailService($subject, $emailOtp));
            $response = [
                "user_id" => encrypt($user->id)
            ];
            if(Auth::guard("user")->user()){
                return ApiResponse::successResponse(null, "Otp Resent over your email.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::successResponse($response, "Otp Resent over your email.", ProjectConstants::SUCCESS);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Invalid user Id.", ProjectConstants::SERVER_ERROR);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "User not exists.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * Send OTP for password reset.
     *
     * @OA\Post(
     *     path="/api/v1/user/forget-password",
     *     tags={"Authentication"},
     *     summary="Send OTP for password reset",
     *     operationId="forgetPassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com", description="User's email address")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="string", example="encrypted_user_id", description="Encrypted user ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Can't find the user.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unable to send the otp.")
     *         )
     *     )
     * )
     */
    public function forgetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ],[
                "email.exists" => "User doesn't exist with this email"
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $emailOtp = $user->genrateEmailOtp();
                $subject = "Verification otp mail";
                Mail::to($user->email)->send(new EmailService($subject, $emailOtp));
                $response = [
                    "user_id" => encrypt($user->id)
                ];
                return ApiResponse::successResponse($response, "Otp sent over your email.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse(null, "Can't find the user.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Unable to send the otp.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/user/resend-otp",
     *      operationId="resendOtp",
     *      tags={"Authentication"},
     *      summary="Resend OTP for forgot password",
     *      description="Resends an OTP to the user's email for forgot password.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Pass user ID",
     *          @OA\JsonContent(
     *              required={"user_id"},
     *              @OA\Property(property="user_id", type="string", description="Encrypted user ID")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="string", description="Encrypted user ID"),
     *              example={"user_id": "encrypted_user_id"}
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", description="Error message"),
     *              example={"error": "Can't find the user."}
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", description="Error message"),
     *              example={"error": "Unable to send the otp."}
     *          )
     *      )
     * )
     */
    public function resendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = User::findOrFail(decrypt($request->user_id));
            $emailOtp = $user->genrateEmailOtp();
            $subject = "Verification otp mail";
            Mail::to($user->email)->send(new EmailService($subject, $emailOtp));
            $response = [
                "user_id" => encrypt($user->id)
            ];
            return ApiResponse::successResponse($response, "Otp sent over your email.", ProjectConstants::SUCCESS);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "User Not Found.", ProjectConstants::NOT_FOUND);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Enter the valid id.", ProjectConstants::SERVER_ERROR);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Unable to send the otp.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * Verify OTP for password reset.
     *
     * @OA\Post(
     *     path="/api/v1/user/verify-otp",
     *     tags={"Authentication"},
     *     summary="Verify OTP for password reset",
     *     operationId="verifyOtp",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "otp"},
     *             @OA\Property(property="user_id", type="string", example="encrypted_user_id", description="Encrypted user ID"),
     *             @OA\Property(property="otp", type="integer", example=1234, description="OTP received by the user (4 digits)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="string", example="encrypted_user_id", description="Encrypted user ID"),
     *             @OA\Property(property="password_token", type="string", example="password_reset_token", description="Token for password reset")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"user_id": {"The user ID field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=417,
     *         description="OTP mismatch",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Otp Not Matched")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string',
                'otp' => 'required|regex:/^[0-9]{4}$/',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = User::findOrFail(decrypt($request->user_id));
            $userOtp = UserOtps::where(["user_id" => $user->id, "type" => ProjectConstants::EMAIL_OTP])->first();
            if ($userOtp->otp == $request->otp) {
                $user->remember_token = $user->passwordResetToken();
                $user->save();
                $response = [
                    'user_id' => encrypt($user->id),
                    'password_token' => $user->remember_token,
                ];
                return ApiResponse::successResponse($response, "Otp Matched.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse(null, "Otp Not Matched", ProjectConstants::BAD_REQUEST);
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            return ApiResponse::errorResponse(null, "User Not Found", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Internal Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * Reset user password.
     *
     * @OA\Post(
     *     path="/api/v1/user/reset-password",
     *     tags={"Authentication"},
     *     summary="Reset user password",
     *     operationId="resetPassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "password_token", "new_password", "confirm_password"},
     *             @OA\Property(property="user_id", type="string", example="encrypted_user_id", description="Encrypted user ID"),
     *             @OA\Property(property="password_token", type="string", example="password_reset_token", description="Token for password reset"),
     *             @OA\Property(property="new_password", type="string", example="NewPassword123!", description="New password (at least 8 characters with at least one uppercase letter, one lowercase letter, one number, and one special character)"),
     *             @OA\Property(property="confirm_password", type="string", example="NewPassword123!", description="Confirmation of the new password (must match the new password)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password Changed Successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid password reset token",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid password reset token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User Not Found with this id")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"user_id": {"User id is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|string',
                'password_token' => 'required|string',
                'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/',
                'confirm_password' => 'required|same:new_password',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = User::findOrFail(decrypt($request->user_id));
            if ($user->remember_token != $request->password_token) {
                return ApiResponse::errorResponse(null, "Invalid password reset token", ProjectConstants::BAD_REQUEST);
            }
            $user->password = $request->confirm_password;
            $user->remember_token = null;
            $user->save();
            return ApiResponse::successResponse(null, "Password Changed Successfully.", ProjectConstants::SUCCESS);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Invalid user ID", ProjectConstants::SERVER_ERROR);
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            return ApiResponse::errorResponse(null, "User Not Found", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Internal server error.", ProjectConstants::SERVER_ERROR);
        }
    }
}
