<?php

namespace App\Http\Controllers\Driver;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\DriverDocumnets;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DriverDocumentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/driver/add-driver-details",
     *     summary="Update Driver Details",
     *     tags={"Driver Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="driver_license_front", type="string", format="binary"),
     *                 @OA\Property(property="driver_license_back", type="string", format="binary"),
     *                 @OA\Property(property="driver_vehicle_registration", type="string", format="binary"),
     *                 @OA\Property(property="driver_vehicle_insurance", type="string", format="binary"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Details updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Driver details updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Required data not provided.")
     *         )
     *     )
     * )
     */
    public function addDriverDetails(Request $request)
    {
        $driver = Auth::guard("drivers")->user();
        if (!$driver) {
            return ApiResponse::errorResponse([], "Unautentiacted", ProjectConstants::UNAUTHENTICATED);
        }
        switch ($driver->step_completed) {
            case ProjectConstants::DRIVER_EMAIL_VERIFIED:
                return $this->addDriverLicence($request);
            case ProjectConstants::DRIVER_LICENSE_ADDED:
                return  $this->addVehicleRegistrartion($request);

            case ProjectConstants::DRIVER_VHECLE_REGISTRATION_ADDED:
                return $this->addVehicleInsurance($request);
            case ProjectConstants::DRIVER_VHECLE_INSURANCE_ADDED:
                return ApiResponse::successResponse([], "Registration Successfull.", ProjectConstants::SUCCESS);
            default:
                return ApiResponse::successResponse([], "Registration Successfull.", ProjectConstants::SUCCESS);
        }
    }

    private function addDriverLicence(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'driver_license_front' => 'required|image|mimes:jpeg,png,jpg,gif,bmp,tiff,webp|max:4048',
                'driver_license_back' => 'required|image|mimes:jpeg,png,jpg,gif,bmp,tiff,webp|max:4048',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $driver = Auth::guard("drivers")->user();
            DriverDocumnets::where("driver_id", $driver->id)->whereIn("type", [ProjectConstants::DRIVING_LICENCE_BACK, ProjectConstants::DRIVING_LICENCE_FRONT])->delete();
            if ($request->has('driver_license_front') && !empty('driver_license_front')) {
                $uploadedFile = $request->file('driver_license_front');
                $diverDocuments = new DriverDocumnets();
                $diverDocuments->document = AwsHelper::uploadFile($uploadedFile, ProjectConstants::DRIVER_DOCUMNETS);
                $diverDocuments->type = ProjectConstants::DRIVING_LICENCE_FRONT;
                $diverDocuments->driver_id = $driver->id;
                $diverDocuments->save();
            }
            if ($request->has('driver_license_back') && !empty('driver_license_back')) {
                $uploadedFile = $request->file('driver_license_back');
                $diverDocuments = new DriverDocumnets();
                $diverDocuments->document = AwsHelper::uploadFile($uploadedFile, ProjectConstants::DRIVER_DOCUMNETS);
                $diverDocuments->type = ProjectConstants::DRIVING_LICENCE_BACK;
                $diverDocuments->driver_id = $driver->id;
                $diverDocuments->save();
            }
            $driver->status = 0;
            $driver->is_admin_approved = 0;
            $driver->step_completed = ProjectConstants::DRIVER_LICENSE_ADDED;
            $driver->save();
            $response = [
                "id" => $driver->id,
                "step_completed" => $driver->step_completed
            ];
            return ApiResponse::successResponse($response, "License Added Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([], "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    private function addVehicleRegistrartion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'driver_vehicle_registration' => 'required|image|mimes:jpeg,png,jpg,gif,bmp,tiff,webp|max:4048',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $driver = Auth::guard("drivers")->user();
            DriverDocumnets::where("driver_id", $driver->id)->where("type", ProjectConstants::VEHICLE_REGISTRATION)->delete();
            if ($request->has('driver_vehicle_registration') && !empty('driver_vehicle_registration')) {
                $uploadedFile = $request->file('driver_vehicle_registration');
                $diverDocuments = new DriverDocumnets();
                $diverDocuments->document = AwsHelper::uploadFile($uploadedFile, ProjectConstants::DRIVER_DOCUMNETS);
                $diverDocuments->type = ProjectConstants::VEHICLE_REGISTRATION;
                $diverDocuments->driver_id = $driver->id;
                $diverDocuments->save();
            }
            $driver->step_completed = ProjectConstants::DRIVER_VHECLE_REGISTRATION_ADDED;
            $driver->save();
            $response = [
                "id" => $driver->id,
                "step_completed" => $driver->step_completed
            ];
            return ApiResponse::successResponse($response, "Vehicle Registration Added Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([], "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    private function addVehicleInsurance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'driver_vehicle_insurance' => 'required|image|mimes:jpeg,png,jpg,gif,bmp,tiff,webp|max:4048',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $driver = Auth::guard("drivers")->user();
            DriverDocumnets::where("driver_id", $driver->id)->where("type", ProjectConstants::VEHICLE_INSURANCE)->delete();
            if ($request->has('driver_vehicle_insurance') && !empty('driver_vehicle_insurance')) {
                $uploadedFile = $request->file('driver_vehicle_insurance');
                $diverDocuments = new DriverDocumnets();
                $diverDocuments->document = AwsHelper::uploadFile($uploadedFile, ProjectConstants::DRIVER_DOCUMNETS);
                $diverDocuments->type = ProjectConstants::VEHICLE_INSURANCE;
                $diverDocuments->driver_id = $driver->id;
                $diverDocuments->save();
            }
            $driver->step_completed = ProjectConstants::DRIVER_VHECLE_INSURANCE_ADDED;
            $driver->save();
            $response = [
                "id" => $driver->id,
                "step_completed" => $driver->step_completed
            ];
            return ApiResponse::successResponse($response, "Registration successfully. We will notify once admin approve your profile", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([], "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

}
