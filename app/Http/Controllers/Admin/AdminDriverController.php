<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Mail\EmailService;
use App\Models\DriverDocumnets;
use App\Models\Drivers;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminDriverController extends Controller
{
    public function getDrivers(Request $request){
        $validator = Validator::make($request->all(), [
            'status' => 'in:0,1,2'
        ]);
        if ($validator->fails()) {
            session()->flash("error","Invalid status.");
            return  redirect()->route("admin.get-driver-list");
        }
        $status = 1;
        $search = '';
        if(isset($request->status) && !empty(isset($request->status))){
            $status = $request->status;
        }
        $drivers = Drivers::where('is_admin_approved', $status)->where("step_completed", 4)->paginate(10);
        return view("Admin.driver.index", compact("drivers","status", "search"));   
    }

    public function addDrivers(Request $request){
        return view("Admin.driver.add");   
    }

    public function addDriversPost(Request $request){
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'phone_number' => 'required|numeric|digits_between:8,15',
            'driving_licence_front' => 'required|image|mimes:jpeg,png,jpg|max:4048',
            'driving_licence_back' => 'required|image|mimes:jpeg,png,jpg|max:4048',
            'vehicle_registration' => 'required|image|mimes:jpeg,png,jpg|max:4048',
            'vehicle_insurance' => 'required|image|mimes:jpeg,png,jpg|max:4048',
        ]);
        if ($validator->fails()) {
            return ApiResponse::validationResponse($validator->errors(), 422);
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
        $driver->is_admin_approved = 1;
        $driver->is_email_verified = 1;
        $driver->is_phone_verified = 1;
        $driver->phone_number = $request->phone_number;
        $driver->email_verified_at = now();
        $driver->phone_verified_at = now();
        $driver->step_completed = 4;
        $driver->password = Drivers::generatePassword();
        if ($request->has('profile_image') && !empty('profile_image')) {
            $uploadedFile = $request->file('profile_image');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/profile', $uploadedFile, $fileName);
            $driver->profile_image =  Storage::url('diver/profile/' . $fileName);
        }
        $driver->save();
        if ($request->has('driving_licence_front') && !empty('driving_licence_front')) {
            $uploadedFile = $request->file('driving_licence_front');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/documents', $uploadedFile, $fileName);
            $driverDocumnets  = DriverDocumnets::where("driver_id", $driver->id)->where("type", 1)->first() ?? new DriverDocumnets();
            $driverDocumnets->driver_id = $driver->id;
            $driverDocumnets->type = 1;
            $driverDocumnets->document = Storage::url('diver/documents/' . $fileName);
            $driverDocumnets->save();
        }
        if ($request->has('driving_licence_back') && !empty('driving_licence_back')) {
            $uploadedFile = $request->file('driving_licence_back');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/documents', $uploadedFile, $fileName);
            $driverDocumnets  = DriverDocumnets::where("driver_id", $driver->id)->where("type", 2)->first() ?? new DriverDocumnets();
            $driverDocumnets->driver_id = $driver->id;
            $driverDocumnets->type = 2;
            $driverDocumnets->document = Storage::url('diver/documents/' . $fileName);
            $driverDocumnets->save();
        }
        if ($request->has('vehicle_registration') && !empty('vehicle_registration')) {
            $uploadedFile = $request->file('vehicle_registration');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/documents', $uploadedFile, $fileName);
            $driverDocumnets  = DriverDocumnets::where("driver_id", $driver->id)->where("type", 3)->first() ?? new DriverDocumnets();
            $driverDocumnets->driver_id = $driver->id;
            $driverDocumnets->type = 3;
            $driverDocumnets->document = Storage::url('diver/documents/' . $fileName);
            $driverDocumnets->save();
        }
        if ($request->has('vehicle_insurance') && !empty('vehicle_insurance')) {
            $uploadedFile = $request->file('vehicle_insurance');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/documents', $uploadedFile, $fileName);
            $driverDocumnets  = DriverDocumnets::where("driver_id", $driver->id)->where("type", 4)->first() ?? new DriverDocumnets();
            $driverDocumnets->driver_id = $driver->id;
            $driverDocumnets->type = 4;
            $driverDocumnets->document = Storage::url('diver/documents/' . $fileName);
            $driverDocumnets->save();
        }
        $response = [
            "redirect_url" => route("admin.get-driver-list")
        ];
        session()->flash("success", "Driver Added sucessfully.");
        return ApiResponse::successResponse($response, "Driver Added sucessfully.", ProjectConstants::SUCCESS);
    }

    public function editDrivers(Request $request){
        $driver = Drivers::findOrFail(decrypt($request->driver_id));
        return view("Admin.driver.edit", compact("driver"));
    }

    public function editDriversPost(Request $request){
        $validator = Validator::make($request->all(), [
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'phone_number' => 'required|numeric|digits_between:8,15',
            'driving_licence_front' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
            'driving_licence_back' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
            'vehicle_registration' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
            'vehicle_insurance' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
        ]);
        if ($validator->fails()) {
            return ApiResponse::validationResponse($validator->errors(), 422);
        }
        $driver = Drivers::findOrFail($request->driver_id);
        if ($driver->phone_number != $request->phone_number) {
            $validator = Validator::make($request->all(), [
                'phone_number' => 'unique:drivers,phone_number,NULL,id,deleted_at,NULL',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors(), 422);
            }
        }
        if ($driver->email != $request->email) {
            $validator = Validator::make($request->all(), [
                'email' => 'unique:drivers,email,NULL,id,deleted_at,NULL',
            ]);
        }
        $driver->name = $request->name;
        $driver->email = $request->email;
        $driver->is_admin_approved = 1;
        $driver->is_email_verified = 1;
        $driver->is_phone_verified = 1;
        $driver->phone_number = $request->phone_number;
        $driver->email_verified_at = now();
        $driver->phone_verified_at = now();
        $driver->password = Drivers::generatePassword();
        if ($request->has('profile_image') && !empty('profile_image')) {
            $uploadedFile = $request->file('profile_image');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/profile', $uploadedFile, $fileName);
            $driver->profile_image =  Storage::url('diver/profile/' . $fileName);
        }
        $driver->save();
        if ($request->has('driving_licence_front') && !empty('driving_licence_front')) {
            $uploadedFile = $request->file('driving_licence_front');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/documents', $uploadedFile, $fileName);
            $driverDocumnets  = DriverDocumnets::where("driver_id", $driver->id)->where("type", 1)->first() ?? new DriverDocumnets();
            $driverDocumnets->driver_id = $driver->id;
            $driverDocumnets->type = 1;
            $driverDocumnets->document = Storage::url('diver/documents/' . $fileName);
            $driverDocumnets->save();
        }
        if ($request->has('driving_licence_back') && !empty('driving_licence_back')) {
            $uploadedFile = $request->file('driving_licence_back');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/documents', $uploadedFile, $fileName);
            $driverDocumnets  = DriverDocumnets::where("driver_id", $driver->id)->where("type", 2)->first() ?? new DriverDocumnets();
            $driverDocumnets->driver_id = $driver->id;
            $driverDocumnets->type = 2;
            $driverDocumnets->document = Storage::url('diver/documents/' . $fileName);
            $driverDocumnets->save();
        }
        if ($request->has('vehicle_registration') && !empty('vehicle_registration')) {
            $uploadedFile = $request->file('vehicle_registration');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/documents', $uploadedFile, $fileName);
            $driverDocumnets  = DriverDocumnets::where("driver_id", $driver->id)->where("type", 3)->first() ?? new DriverDocumnets();
            $driverDocumnets->driver_id = $driver->id;
            $driverDocumnets->type = 3;
            $driverDocumnets->document = Storage::url('diver/documents/' . $fileName);
            $driverDocumnets->save();
        }
        if ($request->has('vehicle_insurance') && !empty('vehicle_insurance')) {
            $uploadedFile = $request->file('vehicle_insurance');
            $fileName = rand() . '_' . time() . '.' . $uploadedFile->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('diver/documents', $uploadedFile, $fileName);
            $driverDocumnets  = DriverDocumnets::where("driver_id", $driver->id)->where("type", 4)->first() ?? new DriverDocumnets();
            $driverDocumnets->driver_id = $driver->id;
            $driverDocumnets->type = 4;
            $driverDocumnets->document = Storage::url('diver/documents/' . $fileName);
            $driverDocumnets->save();
        }
        $response = [
            "redirect_url" => route("admin.get-driver-list")
        ];
        session()->flash("success", "Driver Added sucessfully.");
        return ApiResponse::successResponse($response, "Driver updated sucessfully.", ProjectConstants::SUCCESS);
    }

    public function viewDrivers(Request $request){
        $driver = Drivers::findOrFail(decrypt($request->driver_id));
        return view("Admin.driver.view", compact("driver"));   
    }

    public function changeDriverStatus(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'driver_id' => 'required|numeric|min:1|exists:drivers,id',
            ]);
            if ($validator->fails()) {
                return  ApiResponse::validationResponse($validator->errors()->toArray(),ProjectConstants::VALIDATION_ERROR);
            }
            $driver = Drivers::findOrFail($request->driver_id);
            $driverStatus = $driver->status;
            
            if($driverStatus == 0) {
                $driver->status = 1;
            } else {
                $driver->tokens()->delete();
                $driver->status = 0;
            }
            $driver->save();
            return ApiResponse::successResponse([],"Status Changed Sucessfully.", ProjectConstants::SUCCESS);
        } catch(ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([],"Driver Not Found.", ProjectConstants::NOT_FOUND);
        } catch(Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([],"Server error.", ProjectConstants::SERVER_ERROR);
        }
    }

    public function verifyDriver(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'driver_id' => 'required|numeric|min:1|exists:drivers,id',
                'status' => 'required|in:1,2'
            ]);
            if ($validator->fails()) {
                return  ApiResponse::validationResponse($validator->errors()->toArray(),ProjectConstants::VALIDATION_ERROR);
            }
            if($request->status == 2){
                $validator = Validator::make($request->all(), [
                    'reason' => 'required|string|min:5|max:255'
                ]);
                if ($validator->fails()) {
                    return  ApiResponse::validationResponse($validator->errors()->toArray(),ProjectConstants::VALIDATION_ERROR);
                }
            }
            $driver = Drivers::findOrFail($request->driver_id);
            if($request->status == 2){
                $driver->is_admin_approved = 2;
                $driver->status = 2;
                $driver->reason = $request->reason;
                //$driver->step_completed = ProjectConstants::DRIVER_EMAIL_VERIFIED;
                $driver->save();
                $subject = "Account Verification Status.";
                $message = $request->reason;
                Mail::to($driver->email)->send(new EmailService($subject,$message,3));
                return ApiResponse::successResponse([],"Driver Request Rejected Sucessfully.", ProjectConstants::SUCCESS);
            }
            $driver->is_admin_approved = 1;
            $driver->status = 1;
            $driver->save();
            $subject = "Account Verification Status.";
            $message = $driver->name;
            Mail::to($driver->email)->send(new EmailService($subject,$message,2));
            return ApiResponse::successResponse([],"Driver Request Approved Sucessfully.", ProjectConstants::SUCCESS);
        } catch(ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([],"Driver Not Found.", ProjectConstants::NOT_FOUND);
        } catch(Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([],"Server error.", ProjectConstants::SERVER_ERROR);
        }
    }
}
