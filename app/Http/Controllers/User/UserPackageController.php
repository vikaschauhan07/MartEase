<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\PackageImages;
use App\Models\Packages;
use App\Models\Pickuppoints;
use App\Models\ReciverDetails;
use App\Models\SenderDetails;
use App\Models\UserSenderDetails;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserPackageController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/user/create-package",
     *     summary="Create a new package",
     *     description="Creates a new package with specified dimensions, type, and area, and calculates the shipping fee based on type.",
     *     tags={"Packages"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"height", "width", "length", "type", "area", "is_verified"},
     *             @OA\Property(property="height", type="number", format="float", example=10.5, description="Height of the package"),
     *             @OA\Property(property="width", type="number", format="float", example=5.0, description="Width of the package"),
     *             @OA\Property(property="length", type="number", format="float", example=12.0, description="Length of the package"),
     *             @OA\Property(property="type", type="integer", enum={1, 2, 3}, example=2, description="Type of the package (1, 2, or 3)"),
     *             @OA\Property(property="area", type="number", format="float", example=52.5, description="Area of the package"),
     *             @OA\Property(property="is_verified", type="boolean", example=true, description="Verification flag; must be true")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Package created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Package Created Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="package", type="object",
     *                     @OA\Property(property="id", type="integer", example=1, description="Package ID"),
     *                     @OA\Property(property="type", type="integer", example=2, description="Package type"),
     *                     @OA\Property(property="shipping_fee", type="number", format="float", example=14, description="Shipping fee based on package type"),
     *                     @OA\Property(property="area", type="number", format="float", example=52.5, description="Area of the package")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or missing required fields",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Please set the key true"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string", example="The height field is required."))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Server Error"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function createPackage(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $validator = Validator::make($request->all(), [
                'height' => 'required|numeric',
                'width' => 'required|numeric',
                'length' => 'required|numeric',
                'type' => 'required|in:1,2,3,4',
                'area' => 'required|numeric',
                'is_verified' => 'required|boolean'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            if ($request->is_verified == true) {
                $timestamp = now()->timestamp; 
                $randomPart = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)); 
                $referenceNumber = 'REF' . $timestamp . $randomPart;
                $package = new Packages();
                $package->height = $request->height;
                $package->width = $request->width;
                $package->length = $request->length;
                $package->type = $request->type;
                $package->area = $request->area;
                $package->user_id = $user ? $user->id : null;
                $package->reference_number = $referenceNumber;
                $package->step = 1;
                $package->status = 0;
                switch ($request->type) {
                    case 1:
                        $package->shipping_fee = 9;
                        break;
                    case 2:
                        $package->shipping_fee = 14;
                        break;
                    case 3:
                        $package->shipping_fee = 18;
                        break;
                    default:
                        $package->shipping_fee = 0;
                        break;
                }
                $package->save();
                $response = [
                    "package" => [
                        "id" => $package->id,
                        "type" => ProjectConstants::PACKAGE_NAME_ARRAY[$package->type] ?? "UNKNOWN",
                        "shipping_fee" => $package->shipping_fee,
                        "area" => $package->area,
                        "reference_number" => $package->reference_number
                    ],
                    "sender" => $user && $user->userSenderDetails && $user->userSenderDetails->senderDetails ? $user->userSenderDetails->senderDetails->only(['id', 'name', 'address', 'phone_number']) : null,
                ];
                return ApiResponse::successResponse($response, "Package Created Successfully", ProjectConstants::SUCCESS);
            }
            return ApiResponse::successResponse(null, "Please set the key true", ProjectConstants::BAD_REQUEST);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/add-sender-details",
     *     summary="Add Sender Details",
     *     description="Add sender details for a package. If `sender_details_id` is provided, it updates the package with existing sender details. Otherwise, it creates new sender details and links them to the package.",
     *     tags={"Packages"},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="sender_details_id", type="integer", example=1, description="Existing sender details ID"),
     *             @OA\Property(property="package_id", type="integer", example=1, description="Package ID"),
     *             @OA\Property(property="name", type="string", example="John Doe", description="Sender name (required if no sender_details_id provided)"),
     *             @OA\Property(property="phone_number", type="string", example="1234567890", description="Sender phone number (required if no sender_details_id provided)"),
     *             @OA\Property(property="email", type="string", example="example@example.com", description="Sender email (optional)"),
     *             @OA\Property(property="province", type="string", example="Ontario", description="Sender province (required if no sender_details_id provided)"),
     *             @OA\Property(property="city", type="string", example="Toronto", description="Sender city (required if no sender_details_id provided)"),
     *             @OA\Property(property="address", type="string", example="123 Street", description="Sender address (required if no sender_details_id provided)"),
     *             @OA\Property(property="near_by_box", type="string", example="Near XYZ park", description="Nearby location hint (optional)"),
     *             @OA\Property(property="save_for_future", type="boolean", example=true, description="Save For the future flag; must be  true or false ")
     *          )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Sender Details Added Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Sender Details Added Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="package", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="integer", example=1),
     *                     @OA\Property(property="shipping_fee", type="number", format="float", example=9.00),
     *                     @OA\Property(property="area", type="number", format="float", example=50.0)
     *                 ),
     *                 @OA\Property(property="sender", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="address", type="string", example="123 Street"),
     *                     @OA\Property(property="phone_number", type="string", example="1234567890")
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function addSenderDetails(Request $request)
    {
        try {
            $sender_details_id = null;
            $user = Auth::guard("user")->user();
            if (isset($request->sender_details_id) && !empty($request->sender_details_id)) {
                $validator = Validator::make($request->all(), [
                    'sender_details_id' => 'required|numeric|min:1|exists:sender_details,id',
                    'package_id' => 'required|numeric|min:1|exists:packages,id',
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $sender_details_id = $request->sender_details_id;
            } else {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|max:20',
                    'phone_number' => 'required|numeric|digits_between:8,15',
                    'email' => 'nullable|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                    'province' => 'required|string',
                    'city' => 'required|string',
                    'address' => 'required|string',
                    'near_by_box' => 'nullable|string',
                    'package_id' => 'required|numeric|min:1|exists:packages,id',
                    'save_for_future' => 'nullable|boolean'
                ]);
                if ($validator->fails()) {
                    return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                }
                $senderDetail = new SenderDetails();
                $senderDetail->user_id = $user->id ?? null;
                $senderDetail->name = $request->name;
                $senderDetail->phone_number = $request->phone_number;
                $senderDetail->email = $request->email;
                $senderDetail->province = $request->province;
                $senderDetail->city = $request->city;
                $senderDetail->address = $request->address;
                $senderDetail->near_by_box = $request->near_by_box;
                $senderDetail->pin_code = $request->pin_code;
                $senderDetail->save();
                if ($request->save_for_future && $user) {
                    $userSenderDetails = UserSenderDetails::where("user_id", $user->id)->first() ?? new UserSenderDetails();
                    $userSenderDetails->sender_details_id = $senderDetail->id;
                    $userSenderDetails->user_id = $user->id;
                    $userSenderDetails->save();
                }
                $sender_details_id = $senderDetail->id;
            }
            $package = Packages::findOrFail($request->package_id);
            $package->sender_details_id = $sender_details_id;
            $package->step = 2;
            $package->save();
            $response = [
                "package" => [
                    "id" => $package->id,
                    "type" => ProjectConstants::PACKAGE_NAME_ARRAY[$package->type] ?? "UNKNOWN",
                    "shipping_fee" => $package->shipping_fee,
                    "area" => $package->area,
                    "reference_number" => $package->reference_number
                ],
                "sender" => $package->senderDetails ? $package->senderDetails->only(['id', 'name', 'address', 'phone_number', 'near_by_box']) : null,
            ];
            return ApiResponse::successResponse($response, "Sender Details Added Sucessfully", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/add-reciver-details",
     *     summary="Add Receiver Details",
     *     description="Add receiver details for a package and link them to the specified package.",
     *     tags={"Packages"},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe", description="Receiver's name"),
     *             @OA\Property(property="phone_number", type="string", example="1234567890", description="Receiver's phone number"),
     *             @OA\Property(property="email", type="string", example="example@example.com", description="Receiver's email (optional)"),
     *             @OA\Property(property="province", type="string", example="Ontario", description="Receiver's province"),
     *             @OA\Property(property="city", type="string", example="Toronto", description="Receiver's city"),
     *             @OA\Property(property="address", type="string", example="456 Avenue", description="Receiver's address"),
     *             @OA\Property(property="package_id", type="integer", example=1, description="Package ID")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Receiver Details Added Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Receiver Details Added Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="package", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="integer", example=1),
     *                     @OA\Property(property="shipping_fee", type="number", format="float", example=10.00),
     *                     @OA\Property(property="area", type="string", example="50.0")
     *                 ),
     *                 @OA\Property(property="sender", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="address", type="string", example="123 Street"),
     *                     @OA\Property(property="phone_number", type="string", example="1234567890")
     *                 ),
     *                 @OA\Property(property="reciver", type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Jane Doe"),
     *                     @OA\Property(property="address", type="string", example="456 Avenue"),
     *                     @OA\Property(property="phone_number", type="string", example="9876543210")
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function addReciverDetails(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:20',
                'phone_number' => 'required|numeric|digits_between:8,15',
                'email' => 'nullable|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'province' => 'required|string',
                'city' => 'required|string',
                'address' => 'required|string',
                'package_id' => 'required|numeric|min:1|exists:packages,id',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $reciverDetail = new ReciverDetails();
            $reciverDetail->user_id = $user->id ?? null;
            $reciverDetail->name = $request->name;
            $reciverDetail->phone_number = $request->phone_number;
            $reciverDetail->email = $request->email;
            $reciverDetail->province = $request->province;
            $reciverDetail->city = $request->city;
            $reciverDetail->address = $request->address;
            $reciverDetail->pin_code = $request->pin_code;
            $reciverDetail->pick_up_point = $request->pick_up_point;
            $reciverDetail->save();
            $package = Packages::findOrFail($request->package_id);
            $package->reciver_details_id = $reciverDetail->id;
            $package->step = 3;
            $package->save();
            $response = [
                "package" => [
                    "id" => $package->id,
                    "type" => ProjectConstants::PACKAGE_NAME_ARRAY[$package->type] ?? "UNKNOWN",
                    "shipping_fee" => $package->shipping_fee,
                    "area" => $package->area,
                    "reference_number" => $package->reference_number
                ],
                "sender" => $package->senderDetails ? $package->senderDetails->only(['id', 'name', 'address', 'phone_number', 'near_by_box']) : null,
                "reciver" => $package->reciverDetails ? $package->reciverDetails->only(['id', 'name', 'address', 'phone_number']) : null,
            ];
            return ApiResponse::successResponse($response, "Receiver Details Added Sucessfully", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/get-city-and-hitchmail-details",
     *     summary="Get City and Hitchmail Details",
     *     description="Fetches province, city, and nearby hitchmail box details.",
     *     tags={"Packages"},
     *     @OA\Parameter(
     *         name="province_id",
     *         in="query",
     *         description="ID of the province",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="city_id",
     *         in="query",
     *         description="ID of the city",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hitchmail Address Details Got Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="province",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Alberta")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="city",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Calgary"),
     *                     @OA\Property(property="province_id", type="integer", example=1)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="near_by_box",
     *                 type="array",
     *                 @OA\Items(type="string", example="Calgary")
     *             ),
     *             @OA\Property(
     *                 property="pickup_points",
     *                 type="array",
     *                 @OA\Items(type="string", example="Calgary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation Error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function getCityAndHitchmailDetails(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'province_id' => 'nullable|numeric|min:1',
                'city_id' => 'nullable|numberic|min:1',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $response = [
                "province" => [
                    [
                        "id" => 1,
                        "name" => "Alberta"
                    ]
                ],
                "city" => [
                    [
                        "id" => 1,
                        "name" => "Calgary",
                        "province_id" => 1
                    ],
                    [
                        "id" => 2,
                        "name" => "Edmonton",
                        "province_id" => 1
                    ]
                ],
                "near_by_box" => [
                    "Calgary",
                    "Edmonton"
                ]
            ];
            return ApiResponse::successResponse($response, "Hitchmail Address Details Got Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    public function getPickupPoints(Request $request)
    {
        try {
            $pickupPoints = Pickuppoints::select("id", "buisness_name","phone_number","address")->paginate(10);
            $response = [
                "pickup_points" => $pickupPoints 
            ];
            return ApiResponse::successResponse($response, "Pickup points got successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/add-payment-to-parcel",
     *     summary="Add payment for a package",
     *     description="Adds payment details for a specific package and returns package and payment details",
     *     tags={"Packages"},
     *     
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"package_id"},
     *             @OA\Property(property="package_id", type="integer", example=1, description="ID of the package to make payment for")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Receiver Details Added Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Receiver Details Added Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="package", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="string", example="Small Package"),
     *                     @OA\Property(property="shipping_fee", type="number", format="float", example=15.00),
     *                     @OA\Property(property="area", type="string", example="Downtown")
     *                 ),
     *                 @OA\Property(property="sender", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="address", type="string", example="123 Elm Street"),
     *                     @OA\Property(property="phone_number", type="string", example="555-1234")
     *                 ),
     *                 @OA\Property(property="reciver", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Jane Smith"),
     *                     @OA\Property(property="address", type="string", example="456 Oak Avenue"),
     *                     @OA\Property(property="phone_number", type="string", example="555-5678")
     *                 ),
     *                 @OA\Property(property="payment", type="object",
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="reference_number", type="string", example="REFHITCH1212121221")
     *                 )
     *             ),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="string", example="The package_id field is required.")
     *             ),
     *             @OA\Property(property="code", type="integer", example=422)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function addPayment(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|numeric|min:1|exists:packages,id',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $package = Packages::findOrFail($request->package_id);
            $package->step = 4;
            $package->status = 1;
            $package->save();
            $response = [
                "package" => [
                    "id" => $package->id,
                    "type" => ProjectConstants::PACKAGE_NAME_ARRAY[$package->type] ?? "UNKNOWN",
                    "shipping_fee" => $package->shipping_fee,
                    "area" => $package->area,
                    "reference_number" => $package->reference_number
                ],
                "sender" => $package->senderDetails ? $package->senderDetails->only(['id', 'name', 'address', 'phone_number', 'near_by_box']) : null,
                "reciver" => $package->reciverDetails ? $package->reciverDetails->only(['id', 'name', 'address', 'phone_number']) : null,
                "payment" => [
                    "status" => 1,
                    "refrence_number" => "REFHITCH1212121221"
                ]
            ];
            return ApiResponse::successResponse($response, "Payment Done Sucessfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/add-images-to-parcel",
     *     summary="Add images to a parcel",
     *     description="Uploads and attaches images to a specific parcel, and returns parcel details with images.",
     *     tags={"Packages"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="package_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the package to which images are being added"
     *             ),
     *         ),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"package_id", "package_images"},
     *                 @OA\Property(property="package_id", type="integer", example=1, description="Package ID"),
     *                 @OA\Property(
     *                     property="package_images",
     *                     type="array",
     *                     @OA\Items(
     *                         type="file",
     *                         format="binary",
     *                         description="Image file for the package"
     *                     ),
     *                     description="Array of exactly 3 images"
     *                 ),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Receiver Details Added Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Receiver Details Added Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="package", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="string", example="Small Package"),
     *                     @OA\Property(property="shipping_fee", type="number", format="float", example=15.00),
     *                     @OA\Property(property="area", type="string", example="Downtown")
     *                 ),
     *                 @OA\Property(property="sender", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="address", type="string", example="123 Elm Street"),
     *                     @OA\Property(property="phone_number", type="string", example="555-1234")
     *                 ),
     *                 @OA\Property(property="reciver", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Jane Smith"),
     *                     @OA\Property(property="address", type="string", example="456 Oak Avenue"),
     *                     @OA\Property(property="phone_number", type="string", example="555-5678")
     *                 ),
     *                 @OA\Property(property="payment", type="object",
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="reference_number", type="string", example="REFHITCH1212121221")
     *                 ),
     *                 @OA\Property(property="package_images", type="array",
     *                     @OA\Items(type="string", example="https://your-cdn.com/images/image1.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="string", example="The package_id field is required.")
     *             ),
     *             @OA\Property(property="code", type="integer", example=422)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function addImagesToParcel(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|numeric|min:1|exists:packages,id',
                'package_images' => 'required|array|min:1|max:3',
                'package_images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:4048',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $package = Packages::findOrFail($request->package_id);
            $package->step = 5;
            $package->status = 1;
            $package->save();
            if ($request->has('package_images') && !empty($request->file('package_images'))) {
                foreach ($request->file('package_images') as $uploadedFile) {
                    $packageImages = new PackageImages();
                    $packageImages->package_id = $package->id;
                    $packageImages->images = AwsHelper::uploadFile($uploadedFile, ProjectConstants::PACKAGE_IMAGES);
                    $packageImages->save();
                }
            }
            $response = [
                "package" => [
                    "id" => $package->id,
                    "type" => ProjectConstants::PACKAGE_NAME_ARRAY[$package->type] ?? "UNKNOWN",
                    "shipping_fee" => $package->shipping_fee,
                    "area" => $package->area,
                    "reference_number" => $package->reference_number
                ],
                "sender" => $package->senderDetails ? $package->senderDetails->only(['id', 'name', 'address', 'phone_number', 'near_by_box']) : null,
                "reciver" => $package->reciverDetails ? $package->reciverDetails->only(['id', 'name', 'address', 'phone_number']) : null,
                "payment" => [
                    "status" => 1,
                    "reference_number" => $package->referenceNumber
                ],
                "package_iamges" => $package->packageImages->map(function ($image) {
                    return asset($image->images);
                })
            ];
            return ApiResponse::successResponse($response, "Images Added Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/complete-parcel",
     *     summary="Add images to a parcel",
     *     description="Uploads and attaches images to a specific parcel, and returns parcel details with images.",
     *     tags={"Packages"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="package_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the package to which images are being added"
     *             ),
     *         ),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"package_id", "package_images"},
     *                 @OA\Property(property="package_id", type="integer", example=1, description="Package ID"),
     *                 @OA\Property(
     *                     property="package_images",
     *                     type="array",
     *                     @OA\Items(
     *                         type="file",
     *                         format="binary",
     *                         description="Image file for the package"
     *                     ),
     *                     description="Array of exactly 3 images"
     *                 ),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Receiver Details Added Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Receiver Details Added Successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="package", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="string", example="Small Package"),
     *                     @OA\Property(property="shipping_fee", type="number", format="float", example=15.00),
     *                     @OA\Property(property="area", type="string", example="Downtown")
     *                 ),
     *                 @OA\Property(property="sender", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="address", type="string", example="123 Elm Street"),
     *                     @OA\Property(property="phone_number", type="string", example="555-1234")
     *                 ),
     *                 @OA\Property(property="reciver", type="object", nullable=true,
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="name", type="string", example="Jane Smith"),
     *                     @OA\Property(property="address", type="string", example="456 Oak Avenue"),
     *                     @OA\Property(property="phone_number", type="string", example="555-5678")
     *                 ),
     *                 @OA\Property(property="payment", type="object",
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="reference_number", type="string", example="REFHITCH1212121221")
     *                 ),
     *                 @OA\Property(property="package_images", type="array",
     *                     @OA\Items(type="string", example="https://your-cdn.com/images/image1.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="code", type="integer", example=200)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="string", example="The package_id field is required.")
     *             ),
     *             @OA\Property(property="code", type="integer", example=422)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error"),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function completePackage(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $validator = Validator::make($request->all(), [
                'package_id' => 'required|numeric|min:1|exists:packages,id',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $package = Packages::findOrFail($request->package_id);
            $package->status = 1;
            $package->save();
            $response = [
                "package" => [
                    "id" => $package->id,
                    "type" => ProjectConstants::PACKAGE_NAME_ARRAY[$package->type] ?? "UNKNOWN",
                    "shipping_fee" => $package->shipping_fee,
                    "area" => $package->area,
                    "reference_number" => $package->reference_number
                ],
                "sender" => $package->senderDetails ? $package->senderDetails->only(['id', 'name', 'address', 'phone_number']) : null,
                "reciver" => $package->reciverDetails ? $package->reciverDetails->only(['id', 'name', 'address', 'phone_number']) : null,
                "payment" => [
                    "status" => 1,
                    "refrence_number" => "REFHITCH1212121221"
                ],
                "package_iamges" => $package->packageImages->map(function ($image) {
                    return asset($image->images);
                })
            ];
            return ApiResponse::successResponse($response, "Reciver Details Added Sucessfully", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/get-parcel-history",
     *     summary="Get Package History",
     *     description="Retrieve the package history for the authenticated user",
     *     operationId="getPackageHistory",
     *     tags={"Packages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful retrieval of package history",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Receiver Details Added Successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", 
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="type", type="string", example="Small Package"),
     *                         @OA\Property(property="shipping_fee", type="number", format="float", example=5.50),
     *                         @OA\Property(property="area", type="string", example="Downtown"),
     *                         @OA\Property(
     *                             property="sender", 
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=101),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             @OA\Property(property="address", type="string", example="123 Main St"),
     *                             @OA\Property(property="phone_number", type="string", example="1234567890"),
     *                             @OA\Property(property="city", type="string", example="City Name")
     *                         ),
     *                         @OA\Property(
     *                             property="receiver", 
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=102),
     *                             @OA\Property(property="name", type="string", example="Jane Doe"),
     *                             @OA\Property(property="address", type="string", example="456 Elm St"),
     *                             @OA\Property(property="phone_number", type="string", example="0987654321"),
     *                             @OA\Property(property="city", type="string", example="Another City")
     *                         ),
     *                         @OA\Property(property="status", type="integer", example=1),
     *                         @OA\Property(property="order_date", type="string", format="date-time", example="14/11/2024 10:30 AM")
     *                     )
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Validation error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function getPacakageHistory(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $packages = Packages::where("user_id", $user->id)
                ->where("status", ">", 0)
                ->where("step", 5)
                ->whereNotNull("sender_details_id")
                ->whereNotNull("reciver_details_id")
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            $customizedPackages = $packages->getCollection()->map(function ($package) use ($user){
                return [
                    'id' => $package->id,
                    'reference_number' => $package->reference_number,
                    'type' => ProjectConstants::PACKAGE_NAME_ARRAY[$package->type] ?? "UNKNOWN",
                    'shipping_fee' => $package->shipping_fee,
                    'area' => $package->area,
                    'sender' => $package->senderDetails ? $package->senderDetails->only(['id', 'name', 'address', 'phone_number', 'city', 'near_by_box']) : null,
                    'receiver' => $package->reciverDetails ? $package->reciverDetails->only(['id', 'name', 'address', 'phone_number', 'city']) : null,
                    'status' => $package->status,
                    'order_date' => Carbon::parse($package->created_at)->setTimezone($user->time_zone)->format("d/m/Y H:i A")
                ];
            });
            $packages->setCollection(collect($customizedPackages));
            return ApiResponse::successResponse($packages, "Successful retrieval of package history", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/track-package",
     *     tags={"Packages"},
     *     summary="Track Package by Reference Number",
     *     description="Retrieve package details and tracking history using a reference number.",
     *     @OA\Parameter(
     *         name="reference_number",
     *         in="query",
     *         required=true,
     *         description="The reference number of the package",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="time_zone",
     *         in="query",
     *         required=true,
     *         description="The time Zone",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Package Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="reference_number", type="string"),
     *             @OA\Property(property="message", type="string", example="Package Message success"),
     *             @OA\Property(property="status", type="string", example="DELIVERED"),
     *             @OA\Property(
     *                 property="package_status",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="date", type="string", format="date-time"),
     *                     @OA\Property(property="box", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Package Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Package with this reference number not found.")
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
    public function trackPackage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reference_number' => 'required|string',
                'time_zone' => 'required|string',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $package = Packages::where("reference_number", $request->reference_number)
                ->where("status", ">", 0)
                ->first();

            if (!$package) {
                return ApiResponse::errorResponse(null, "Package with this reference number not found.", ProjectConstants::NOT_FOUND);
            }
            $response = [
                "id" => $package->id,
                "reference_number" => $package->reference_number,
                "status" => "Created",
                "message" => "Parcel Created Successfully.",
                "package_status" => [
                    [
                        "title" => "Shipment Created",
                        "date" => Carbon::parse($package->created_at)->setTimezone($request->time_zone)->format("d/m/Y H:i A"),
                        "box" => $package->senderDetails->near_by_box
                    ]
                ]
            ];
            return ApiResponse::successResponse($response, "Successful retrieval of package history", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/get-package-details",
     *     tags={"Packages"},
     *     summary="Track Package by Reference Number",
     *     description="Retrieve package details and tracking history using a reference number.",
     *     @OA\Parameter(
     *         name="reference_number",
     *         in="query",
     *         required=true,
     *         description="The reference number of the package",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Package Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="reference_number", type="string"),
     *             @OA\Property(property="message", type="string", example="Package Message success"),
     *             @OA\Property(property="status", type="string", example="DELIVERED"),
     *             @OA\Property(
     *                 property="package_status",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="date", type="string", format="date-time"),
     *                     @OA\Property(property="box", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Package Not Found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Package with this reference number not found.")
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
    public function getPackageDtails(Request $request)
    {
        try {
            $package = Packages::where("reference_number", $request->reference_number)->first();

            if (!$package) {
                return ApiResponse::errorResponse(null, "Package with this reference number not found.", ProjectConstants::NOT_FOUND);
            }
            $response = [
                "package" => [
                    "id" => $package->id,
                    "type" => ProjectConstants::PACKAGE_NAME_ARRAY[$package->type] ?? "UNKNOWN",
                    "shipping_fee" => $package->shipping_fee,
                    "area" => $package->area,
                    "reference_number" => $package->reference_number,
                    "step" => $package->step
                ],
                "sender" => $package->senderDetails ? $package->senderDetails->only(['id', 'name', 'address', 'phone_number']) : null,
                "reciver" => $package->reciverDetails ? $package->reciverDetails->only(['id', 'name', 'address', 'phone_number']) : null,
                "payment" => [
                    "status" => 1,
                    "reference_number" => $package->reference_number
                ],
                "package_iamges" => $package->packageImages->map(function ($image) {
                    return asset($image->images);
                })
            ];
            
            return ApiResponse::successResponse($response, "Successful retrieval of package history", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

}
