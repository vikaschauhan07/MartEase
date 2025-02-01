<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Drivers;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Packages;
use App\Models\Trips;
use App\Models\Trailers;

class AdminDashboardController extends Controller
{
    public function index(){
        return view("Admin.employee.index");
        $userCount = User::where("status", 1)->count();
        $driverCount = Drivers::get();
        $packages = Packages::get();
        $trips = Trips::get();
        $trailer = Trailers::get();
        $packageCounts = [
            "pending" => $packages->where("status", 1)->count(),
            "processed" => $packages->where("status", 2)->count(),
            "delivered" => $packages->where("status", 5)->count(),
            "calagaryPackages" =>[
                "inTransit" => $packages->where("from_city", "Calgary")->where("status", 3)->count(),
                "arrived" => $packages->where("to_city", "Calgary")->where("status", 4)->count(),
            ],
            "edmontonPackages" =>[
                "inTransit" => $packages->where("from_city", "Edmonton")->where("status", 3)->count(),
                "arrived" => $packages->where("to_city", "Edmonton")->where("status", 4)->count(),
            ]
        ];
        $driverCount = [
            "totalDriver" => $driverCount->where("step_completed", 4)->where("is_admin_approved", "!=", 2)->count(),
            "approvedDriver" => $driverCount->where("is_admin_approved", 1)->count(),
            "pendingDriver" => $driverCount->where("is_admin_approved", 0)->where("step_completed", 4)->count()
        ];

        $tripCount = [
            "tripCount" => $trips->count(),
            "unbookedTrips" => $trips->whereNull("driver_id")->count()
        ];

        $trailerCount = [
            "totalTrailer" => $trailer->count(),
            "inTransitTrailer" => $trailer->where("status", 3)->count()
        ];
        return view("Admin.employee.index", compact("driverCount", "userCount", "packageCounts","tripCount","trailerCount"));
    }
    
    public function adminProfileChangeView(Request $request)
    {
        try {
            return view('Admin.profile.profile-change');
        } catch (Exception $ex) {
            Log::error($ex);
            session()->flash("error", "Server error.");
            return redirect()->back();
        }
    }

    public function adminPasswordChangeView(Request $request)
    {
        try {
            return view('Admin.profile.password-change');
        } catch (Exception $ex) {
            Log::error($ex);
            session()->flash("error", "Server error.");
            return redirect()->back();
        }
    }

    public function adminProfileChange(Request $request)
    {
        try {
            $rules = [
                'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4048',
                'name' => 'required|string|min:5|max:35|regex:/^[a-zA-Z\s]+$/',
            ];
            $messages = [
                'profile_pic.image' => 'The selected file must be an image (.jpeg, .png, .jpg, or .gif).',
                'profile_pic.mimes' => 'The selected file must be a (.jpeg, .png, .jpg, or .gif) image.',
                'profile_pic.max' => 'The selected file may not be greater than :max kilobytes.',
                'name.required' => 'The name field is required.',
                'name.string' => 'The name must be a string.',
                'name.min' => 'The name must be at least :min characters.',
                'name.max' => 'The name may not be greater than :max characters.',
                'name.regex' => 'The name may only contain letters and spaces.',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                // session()->flash("error", "Validation Error.");
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $admin = Auth::guard('admin')->user();

            $admin->name = $request->name;
            if ($request->has('profile_pic') && !empty('profile_pic')) {
                $uploadedFile = $request->file('profile_pic');
                $admin->profile_pic =  AwsHelper::uploadFile($uploadedFile, ProjectConstants::ADMIN_PROFILE);
            }
            $admin->save();
            session()->flash("success", "Profile updated successfully.");
            return redirect()->back();
        } catch (Exception $ex) {
            Log::error($ex);
            session()->flash("error", "Server error.");
            return redirect()->back();
        }
    }

    public function adminPasswordChange(Request $request)
    {
        try {
            $rules = [
                'old_password' => 'required',
                'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]+$/',
                'confirm_password' => 'required|same:new_password',
            ];

            $messages = [
                'new_password.required' => 'The new password field is required.',
                'new_password.min' => 'The new password must be at least :min characters.',
                'new_password.regex' => 'The new password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
                'confirm_password.required' => 'The confirm password field is required.',
                'confirm_password.same' => 'The confirm password must match the new password.',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors(), 422);
            }
            $admin = Auth::guard('admin')->user();
            if (Hash::check($request->old_password, $admin->password)) {
                $admin->password = $request->new_password;
                $admin->save();
                session()->flash("success", "Password updated successfully");
                return ApiResponse::successResponse(null, 'Password updated successfully', 200);
            }
            return ApiResponse::errorResponse(null, "Old Password not Matched.Please try again.", 400);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", 500);
        }
    }

    public function adminLogout(Request $request) {
        Auth::guard('admin')->logout();
    
        $request->session()->flush();
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        session()->flash('success', 'Logged Out Successfully.');
    
        return redirect()->route('admin.login');
    }
}
