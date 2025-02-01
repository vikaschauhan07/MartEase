<?php

namespace App\Http\Controllers\Admin;

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

class AdminDashboardController extends Controller
{
    public function index(){
        $userCount = User::where("status", 1)->count();
        $driverCount = Drivers::where("is_admin_approved", 1)->count();

        return view("Admin.employee.index", compact("driverCount", "userCount"));
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
                'name' => 'required|string|min:5|max:50|regex:/^[a-zA-Z\s]+$/',
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
                session()->flash("error", "Validation Error.");
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                session()->flash("error", "Unautherised");
                return redirect()->back();
            }
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
                session()->flash("error", "Validation Error.");
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $admin = Auth::guard('admin')->user();
            if (empty($admin)) {
                session()->flash("error", "Unautherised");
                return redirect()->back();
            }
            if (Hash::check($request->old_password, $admin->password)) {
                $admin->password = $request->new_password;
                if ($admin->save()) {
                    session()->flash('success', 'Password updated successfully');
                    return redirect()->back();
                }
            }
            session()->flash("error", "Old Password not Matched.Please try again.");
            return redirect()->back();
        } catch (Exception $ex) {
            Log::error($ex);
            session()->flash("error", "Server Busy.");
            return redirect()->back();
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
