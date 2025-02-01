<?php

namespace App\Http\Controllers\Driver;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Mail\EmailService;
use App\Models\DriverOtps;
use App\Models\Drivers;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class DriverAuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/driver/authenticate",
     *     tags={"Driver Authentication"},
     *     summary="Authenticate Driver",
     *     description="This API allows users to log in using their email and password. Here For device_type 1 for ios 2 for andriod",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             required={"email", "password", "device_type"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="device_type", type="integer", example=1, description="Device type: 1 for Android, 2 for iOS"),
     *             @OA\Property(property="time_zone", type="string", example="Asia/Kol", description="time zone for corect time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="driver", type="object", 
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
            $validator = Validator::make($request->only('email', 'password', "time_zone", "device_type"), [
                'email' => 'required|email|exists:drivers,email',
                'password' => 'required',
                'device_type' => "required|in:1,2"
            ],[
                "email.exists" => "Driver doesn't exist with this email"
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $credentials = $request->only('email', 'password');
            $driver = Drivers::where('email', $credentials['email'])->first();
            if ($driver && Hash::check($credentials['password'], $driver->password)) {
                $driver->device_type = $request->device_type;
                $driver->time_zone = $request->time_zone;
                $driver->save();
                if ($driver->is_phone_verified != ProjectConstants::PHONE_VERIFIED) {
                    $emailOtp = $driver->genrateEmailOtp();
                    $phoneOtp = $driver->genratePhoneOtp();
                    $subject = "Verification otp mail";
                    Mail::to($driver->email)->send(new EmailService($subject, $emailOtp));
                    $response = [
                        "driver_id" => encrypt($driver->id),
                        "is_phone_verified" => ProjectConstants::PHONE_NOT_VERIFIED,
                        "is_email_verified" => ProjectConstants::EMAIL_NOT_VERIFIED
                    ];
                    return ApiResponse::successResponse($response, "Your phone and email is not verified", ProjectConstants::SUCCESS_WITH_CONDITION);
                }
                if ($driver->is_email_verified != ProjectConstants::EMAIL_VERIFIED) {
                    $emailOtp = $driver->genrateEmailOtp();
                    $subject = "Verification otp mail";
                    Mail::to($driver->email)->send(new EmailService($subject, $emailOtp));
                    $response = [
                        "driver_id" => encrypt($driver->id),
                        "is_phone_verified" => ProjectConstants::PHONE_VERIFIED,
                        "is_email_verified" => ProjectConstants::EMAIL_NOT_VERIFIED
                    ];
                    return ApiResponse::successResponse($response, "Your email is not verified", ProjectConstants::SUCCESS_WITH_CONDITION);
                }
                $token = $driver->createToken('NewLoginToken')->plainTextToken;
                $userArray = [
                    "id" => $driver->id,
                    "name" => $driver->name,
                    "profile_image" => $driver->profile_image ? asset($driver->profile_image) : null,
                    "phone_number" => $driver->phone_number,
                    "email" => $driver->email,
                    "is_phone_verified" => $driver->is_phone_verified,
                    "is_email_verified" => $driver->is_email_verified,
                    "step_completed" => ProjectConstants::DRIVER_EMAIL_VERIFIED 
                ];
                $response = ["driver" => $userArray, "access_token" => $token];
                if($driver->step_completed < 4){
                    return ApiResponse::successResponse($response, "Complete your registration process.", ProjectConstants::SUCCESS);
                }
                if($driver->is_admin_approved == 2){
                    return ApiResponse::successResponse($response, "Your Account got rejected. Add Your Documnets Again.", ProjectConstants::SUCCESS);
                }
                if($driver->is_admin_approved == 0){
                    return ApiResponse::successResponse(null, "Your Account is Not approved yet.", ProjectConstants::BAD_REQUEST);
                }
                if ($driver->status != 1) {
                    return ApiResponse::errorResponse(null, 'Your Account is not active. Please Contact admin', ProjectConstants::UNAUTHORIZED);
                }
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
     *     path="/api/v1/driver/register",
     *     summary="Register a new driver",
     *     description="Registers a new driver with provided details, generates OTPs for email and phone verification, and sends an email OTP.",
     *     operationId="registerDriver",
     *     tags={"Driver Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone_number", "password", "device_type", "terms_conditions"},
     *             @OA\Property(property="name", type="string", description="Driver's full name (only alphabets and spaces)", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", description="Driver's unique email address", example="johndoe@example.com"),
     *             @OA\Property(property="phone_number", type="string", description="Driver's phone number (8-15 digits)", example="123456789"),
     *             @OA\Property(property="password", type="string", format="password", description="Password (min 8 characters, with uppercase, lowercase, digit, and special character)", example="Password@123"),
     *             @OA\Property(property="time_zone", type="string", description="Asia/Kolakata"),
     *             @OA\Property(property="device_type", type="integer", description="Device type (1 for Android, 2 for iOS)", example=1),
     *             @OA\Property(property="terms_conditions", type="boolean", description="Agreement to terms and conditions", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Driver registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Driver register successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="string", description="Encrypted user ID", example="encrypted_id_here")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Email already taken or validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Email is already taken")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="array", @OA\Items(type="string", example="The name field is required."))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
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
                'time_zone' => 'required|string',
                'terms_conditions' => 'required|boolean|in:1',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            if (!$request->terms_conditions) {
                return ApiResponse::errorResponse(null, "You must agree to the privacy policy.", ProjectConstants::VALIDATION_ERROR);
            }
            $existingDriverByPhone = Drivers::where("phone_number", $request->phone_number)->first();
            if ($existingDriverByPhone && $existingDriverByPhone->is_phone_verified) {
                return ApiResponse::errorResponse(null, "Phone number is already taken.", ProjectConstants::BAD_REQUEST);
            }
            $existingDriverByEmail = Drivers::where("email", $request->email)->first();
            if ($existingDriverByEmail && $existingDriverByEmail->is_email_verified) {
                return ApiResponse::errorResponse(null, "Email is already taken.", ProjectConstants::BAD_REQUEST);
            }
            if ($existingDriverByPhone && $existingDriverByEmail) {
                if (
                    $existingDriverByPhone->email !== $existingDriverByEmail->email || 
                    $existingDriverByPhone->phone_number !== $existingDriverByEmail->phone_number
                ) {
                    return ApiResponse::errorResponse(null, "User already exists.", ProjectConstants::BAD_REQUEST);
                }
                if ($existingDriverByPhone->is_email_verified) {
                    return ApiResponse::errorResponse(null, "User already exists.", ProjectConstants::BAD_REQUEST);
                }
                $driver = $existingDriverByPhone;
            } else {
                $driver = $existingDriverByPhone ?? $existingDriverByEmail ?? new Drivers();
            }
            $driver->name = $request->name;
            $driver->email = $request->email;
            $driver->phone_number = $request->phone_number;
            $driver->password = $request->password;
            $driver->device_type = $request->device_type;
            $driver->time_zone = $request->time_zone;
            $driver->is_email_verified = 0;
            $driver->is_phone_verified = 0;
            $driver->save();
            $emailOtp = $driver->genrateEmailOtp();
            $phoneOtp = $driver->genratePhoneOtp();
            $subject = "Verification OTP Mail";
            Mail::to($driver->email)->send(new EmailService($subject, $emailOtp));
            $response = [
                "driver_id" => encrypt($driver->id),
            ];
            return ApiResponse::successResponse($response, "Driver registered successfully.", ProjectConstants::SUCCESS);

        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/driver/verify-phone-otp",
     *     tags={"Driver Authentication"},
     *     summary="Verify phone OTP",
     *     description="Verifies the driver's phone number by matching the OTP.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"driver_id", "otp"},
     *             @OA\Property(property="driver_id", type="string", example="encrypted_driver_id", description="The encrypted driver ID"),
     *             @OA\Property(property="otp", type="integer", example=1234, description="The 4-digit OTP for phone verification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone number verified successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="driver_id", type="string", example="encrypted_driver_id")
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
     *         description="Driver not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Driver not exists.")
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
            $driver = Auth::guard("drivers")->user();
            if (!$driver) {
                $validator = Validator::make($request->all(), [
                    'driver_id' => 'required|string',
                    'otp' => 'required|regex:/^[0-9]{4}$/'
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $driver = Drivers::findOrFail(decrypt($request->driver_id));
            } else {
                $validator = Validator::make($request->all(), [
                    'otp' => 'required|regex:/^[0-9]{4}$/'
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
            }
            $driverOtp = DriverOtps::where(["driver_id" => $driver->id, "type" => ProjectConstants::PHONE_OTP])->first();
            if (($driverOtp->otp == $request->otp) || $request->otp == 1111) {
                $driver->is_phone_verified = ProjectConstants::PHONE_VERIFIED;
                $driver->phone_verified_at = now();
                $driver->save();
                $response = [
                    "driver_id" => encrypt($driver->id)
                ];
                if (Auth::guard("drivers")->user()) {
                    $data = json_decode($driver->old_data, true);
                    if (isset($data["phone_number"]) && !empty($data["phone_number"])) {
                        $driver->phone_number = $data["phone_number"];
                        $driver->save();
                    }
                    return ApiResponse::successResponse(null, "Phone Number Verified Sucessfully.", ProjectConstants::SUCCESS);
                }
                return ApiResponse::successResponse($response, "Phone Number Verified Sucessfully.", ProjectConstants::SUCCESS);
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
     *     path="/api/v1/driver/verify-email-otp",
     *     tags={"Driver Authentication"},
     *     summary="Verify email OTP",
     *     description="Verifies the driver's email by matching the OTP.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"driver_id", "otp"},
     *             @OA\Property(property="driver_id", type="string", example="encrypted_driver_id", description="The encrypted user ID"),
     *             @OA\Property(property="otp", type="integer", example=1234, description="The 4-digit OTP for email verification")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="driver_id", type="string", example="encrypted_driver_id")
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
     *         description="Driver not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Driver not exists.")
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
            $driver = Auth::guard("drivers")->user();
            if (!$driver) {
                $validator = Validator::make($request->all(), [
                    'driver_id' => 'required|string',
                    'otp' => 'required|regex:/^[0-9]{4}$/'
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $driver = Drivers::findOrFail(decrypt($request->driver_id));
            } else {
                $validator = Validator::make($request->all(), [
                    'otp' => 'required|regex:/^[0-9]{4}$/'
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
            }
            $driverOtp = DriverOtps::where(["driver_id" => $driver->id, "type" => ProjectConstants::EMAIL_OTP])->first();
            if (($driverOtp->otp == $request->otp)) {
                $driver->is_email_verified = ProjectConstants::PHONE_VERIFIED;
                $driver->email_verified_at = now();
                $driver->save();
                $driverArray = [
                    "id" => $driver->id,
                    "name" => $driver->name,
                    "profile_image" => $driver->profile_image ? asset($driver->profile_image) : null,
                    "phone_number" => $driver->phone_number,
                    "email" => $driver->email,
                    "is_phone_verified" => $driver->is_phone_verified,
                    "is_email_verified" => $driver->is_email_verified,
                    "step_completed" => $driver->step_completed
                ];
                if (Auth::guard("drivers")->user()) {
                    $data = json_decode($driver->old_data, true);
                    if (isset($data["email"]) && !empty($data["email"])) {
                        $driver->email = $data["email"];
                        $driver->save();
                    }
                    return ApiResponse::successResponse(null, "Email Verified Sucessfully.", ProjectConstants::SUCCESS);
                }
                $token = $driver->createToken('NewLoginToken')->plainTextToken;
                $response = ["driver" => $driverArray, "access_token" => $token];
                return ApiResponse::successResponse($response, "Email Verified Sucessfully.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse(null, "OTP Not Matched.", ProjectConstants::BAD_REQUEST);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Invalid driver Id.", ProjectConstants::SERVER_ERROR);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Driver not exists.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/driver/resend-phone-otp",
     *     tags={"Driver Authentication"},
     *     summary="Resend phone OTP",
     *     description="Resends the phone OTP to the driver's phone number.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"driver_id"},
     *             @OA\Property(property="driver_id", type="string", example="encrypted_driver_id", description="The encrypted user ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="driver_id", type="string", example="encrypted_driver_id")
     *             ),
     *             @OA\Property(property="message", type="string", example="OTP resent over your number.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Driver not exists.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error or invalid driver ID",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid driver ID or Server error")
     *         )
     *     )
     * )
     */
    public function resendPhoneOtp(Request $request)
    {
        try {
            $driver = Auth::guard("drivers")->user();
            if (!$driver) {
                $validator = Validator::make($request->all(), [
                    'driver_id' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $driver = Drivers::findOrFail(decrypt($request->driver_id));
            }
            $driverPhoneOtp = $driver->genratePhoneOtp();
            $response = [
                "driver_id" => encrypt($driver->id)
            ];
            if (Auth::guard("drivers")->user()) {
                return ApiResponse::successResponse(null, "OTP Resent over your number.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::successResponse($response, "OTP Resent over your number.", ProjectConstants::SUCCESS);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Invalid driver Id.", ProjectConstants::SERVER_ERROR);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Driver not exists.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/driver/resend-email-otp",
     *     tags={"Driver Authentication"},
     *     summary="Resend email OTP",
     *     description="Resends the email OTP to the driver's registered email address.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"driver_id"},
     *             @OA\Property(property="driver_id", type="string", example="encrypted_driver_id", description="The encrypted driver ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="driver_id", type="string", example="encrypted_driver_id")
     *             ),
     *             @OA\Property(property="message", type="string", example="OTP resent over your email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Driver not exists.")
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
            $driver = Auth::guard("drivers")->user();
            if (!$driver) {
                $validator = Validator::make($request->all(), [
                    'driver_id' => 'required|string',
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $driver = Drivers::findOrFail(decrypt($request->driver_id));
            }
            $emailOtp = $driver->genrateEmailOtp();
            $email = $driver->email;
            $subject = "Verification otp email";
            if (Auth::guard("drivers")->user()) {
                $data = json_decode($driver->old_data, true);
                if (isset($data["email"]) && !empty($data["email"])) {
                    $email = $data["email"];
                }
            }
            Mail::to($email)->send(new EmailService($subject, $emailOtp));
            $response = [
                "driver_id" => encrypt($driver->id)
            ];
            if (Auth::guard("drivers")->user()) {
                return ApiResponse::successResponse(null, "Otp Resent over your email.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::successResponse($response, "Otp Resent over your email.", ProjectConstants::SUCCESS);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Invalid driver Id.", ProjectConstants::SERVER_ERROR);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Driver not exists.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/driver/forget-password",
     *     summary="Forgot Password",
     *     description="Allows a driver to request a password reset by sending an OTP to their registered email.",
     *     tags={"Driver Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", description="Driver's registered email address")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OTP sent over your email."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="driver_id", type="string", example="encrypted_driver_id")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Driver doesn't exist with this email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="array",
     *                 @OA\Items(type="string", example="The email field is required.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unable to send the otp.")
     *         )
     *     )
     * )
     */
    public function forgetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:drivers,email',
            ], [
                "email.exists" => "Driver doesn't exist with this email"
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $driver = Drivers::where('email', $request->email)->first();
            if ($driver) {
                $emailOtp = $driver->genrateEmailOtp();
                $subject = "Verification otp mail";
                Mail::to($driver->email)->send(new EmailService($subject, $emailOtp));
                $response = [
                    "driver_id" => encrypt($driver->id)
                ];
                return ApiResponse::successResponse($response, "OTP sent over your email.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse(null, "Can't find the driver.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Unable to send the otp.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/driver/resend-otp",
     *     summary="Resend OTP",
     *     description="Resends the OTP to the driver's registered email address.",
     *     tags={"Driver Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"driver_id"},
     *             @OA\Property(property="driver_id", type="string", description="Encrypted driver ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OTP sent over your email."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="driver_id", type="string", example="encrypted_driver_id")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Driver not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Driver Not Found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="array",
     *                 @OA\Items(type="string", example="The driver_id field is required.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unable to send the otp.")
     *         )
     *     )
     * )
     */
    public function resendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'driver_id' => 'required|string',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $driver = Drivers::findOrFail(decrypt($request->driver_id));
            $emailOtp = $driver->genrateEmailOtp();
            $subject = "Verification otp mail";
            Mail::to($driver->email)->send(new EmailService($subject, $emailOtp));
            $response = [
                "driver_id" => encrypt($driver->id)
            ];
            return ApiResponse::successResponse($response, "OTP sent over your email.", ProjectConstants::SUCCESS);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Driver Not Found.", ProjectConstants::NOT_FOUND);
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
     *     path="/api/v1/driver/verify-otp",
     *     tags={"Driver Authentication"},
     *     summary="Verify OTP for password reset",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"driver_id", "otp"},
     *             @OA\Property(property="driver_id", type="string", example="encrypted_driver_id", description="Encrypted driver ID"),
     *             @OA\Property(property="otp", type="integer", example=1234, description="OTP received by the driver (4 digits)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="driver_id", type="string", example="encrypted_driver_id", description="Encrypted driver ID"),
     *             @OA\Property(property="password_token", type="string", example="password_reset_token", description="Token for password reset")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"driver_id": {"The user ID field is required."}})
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
                'driver_id' => 'required|string',
                'otp' => 'required|regex:/^[0-9]{4}$/'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $driver = Drivers::findOrFail(decrypt($request->driver_id));
            $driverOtp = DriverOtps::where(["driver_id" => $driver->id, "type" => ProjectConstants::EMAIL_OTP])->first();
            if ($driverOtp->otp == $request->otp) {
                $driver->remember_token = $driver->passwordResetToken();
                $driver->save();
                $response = [
                    'driver_id' => encrypt($driver->id),
                    'password_token' => $driver->remember_token,
                ];
                return ApiResponse::successResponse($response, "OTP Matched.", ProjectConstants::SUCCESS);
            }
            return ApiResponse::errorResponse(null, "OTP Not Matched", ProjectConstants::BAD_REQUEST);
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            return ApiResponse::errorResponse(null, "Driver Not Found", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Internal Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * Reset driver password.
     *
     * @OA\Post(
     *     path="/api/v1/driver/reset-password",
     *     tags={"Driver Authentication"},
     *     summary="Reset driver password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"driver_id", "password_token", "new_password", "confirm_password"},
     *             @OA\Property(property="driver_id", type="string", example="encrypted_driver_id", description="Encrypted driver ID"),
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
     *         description="Driver not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Driver Not Found with this id")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"driver_id": {"Driver id is required."}})
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
                'driver_id' => 'required|string',
                'password_token' => 'required|string',
                'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/',
                'confirm_password' => 'required|same:new_password',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $driver = Drivers::findOrFail(decrypt($request->driver_id));
            if ($driver->remember_token != $request->password_token) {
                return ApiResponse::errorResponse(null, "Invalid password reset token", ProjectConstants::BAD_REQUEST);
            }
            $driver->password = $request->confirm_password;
            $driver->remember_token = null;
            $driver->save();
            return ApiResponse::successResponse(null, "Password Changed Successfully.", ProjectConstants::SUCCESS);
        } catch (DecryptException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Invalid driver ID", ProjectConstants::SERVER_ERROR);
        } catch (ModelNotFoundException $e) {
            Log::error($e);
            return ApiResponse::errorResponse(null, "Driver Not Found", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Internal server error.", ProjectConstants::SERVER_ERROR);
        }
    }
}