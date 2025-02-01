<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Mail\EmailService;
use App\Models\Admin;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class AdminAuthController extends Controller
{
    public function index()
    {
        if(Auth::guard("admin")->user() && !empty(Auth::guard("admin")->user())){
            // session()->flash("success", "You are already signed in.");
            return redirect()->route("admin.dashboard");   
        } 
        return view('Admin.auth.login');
    }

    public function test(){
        try{

        
        $emailOtp = 4545;
        $subject = "Your One-Time Password (OTP) for email verification ";
        $name = "Test";
        Mail::to("test@yopmail.com")->send(new EmailService($subject, $name, $emailOtp));
        return "Success";
    } catch(Exception $ex){
        dd($ex);
    }
    }
    public function authenticate(Request $request)
    {
        try {
            $validator = Validator::make($request->only('email', 'password'), [
                'email' => 'required|email|exists:admin,email',
                'password' => 'required',
            ], [
                'email.required' => 'The email field is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.exists' => 'The email does not exist in our records.', 
                'password.required' => 'The password field is required.',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors(), 422);
            }
            $credentials = $request->only('email', 'password');
            if (Auth::guard('admin')->attempt($credentials)) {
                $admin = Auth::guard("admin")->user();
                if ($admin->status != 1) {
                    session()->flash("error", "Your account is not active please contact Super Admin.");
                    Auth::guard("admin")->logout();
                    return redirect()->back();
                }
                return ApiResponse::successResponse(["redirect_url" => route('admin.dashboard')], "Invalid Credentials", 200);
            }
            return ApiResponse::errorResponse([], "Invalid Credentials", 400);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([], "Server Error.", 500);
        }
    }

    public function forgetPassword()
    {
        if(Auth::guard("admin")->user()){
         return redirect()->route("admin.dashboard");   
        }
        return view('Admin.auth.forget');
    }

    public function sendOtp(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email|exists:admin,email',
            ];
            $messages = [
                'email.required' => 'The email field is required.',
                'email.email' => 'Please enter a valid email address.',
                'email.exists' => 'The email address does not exist in our records.',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                // session()->flash("error", "Validation Error.");
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $admin = Admin::where('email', $request->email)->first();
            if (!empty($admin)) {
                $otp = mt_rand(1000, 9999);;
                $admin->otp = $otp;
                if ($admin->save()) {
                    $emailOtp = $otp;
                    $subject = "Your One-Time Password (OTP) for email verification ";
                    $name = $admin->name;
                    Mail::to($admin->email)->send(new EmailService($subject, $name, $emailOtp));
                    session()->flash("success", "OTP sent over your mail.");
                    return ApiResponse::successResponse(["redirect_url" => route('admin.enter-otp', ['id' => encrypt($admin->id)])], "Otp sent over your mail",200);
                    // return redirect()->route('admin.enter-otp', ['id' => encrypt($admin->id)]);
                }
            }
            session()->flash("error", "User Not Existed.");
            return redirect()->route('admin.login');
        } catch (Exception $ex) {
            session()->flash("error", "Something went wrong.Please try again.");
            return redirect()->route('admin.login');
        }
    }

    public function enterOtp($id)
    {
        try {
            $id = decrypt($id);
            $user = Admin::findOrFail($id);
            return view('Admin.auth.enter-otp', compact('user'));
        } catch(DecryptException $ex){
            Log::error($ex);
            session()->flash("error", "Invalid Id.");
            return redirect()->back();
        } catch(ModelNotFoundException $ex){
            Log::error($ex);
            session()->flash("error", "Can't find user.");
            return redirect()->back();
        } catch (Exception $ex) {
            session()->flash("error", "Server Busy. Please Try Again");
            return redirect()->back();
        }
    }

    public function resendOtp($id)
    {
        try {
            $id = decrypt($id);
            $admin = Admin::findOrFail($id);
            $otp = mt_rand(1000, 9999);
            $admin->otp = $otp;
            if ($admin->save()) {
                $emailOtp = $otp;
                $subject = "Your One-Time Password (OTP) for email verification ";
                $name = $admin->name;
                Mail::to($admin->email)->send(new EmailService($subject, $name, $emailOtp));                   
                session()->flash("success", "OTP re-sent over your mail.");
                return redirect()->back();
            }
        } catch(DecryptException $ex){
            Log::error($ex);
            session()->flash("error", "Invalid Id.");
            return redirect()->back();
        } catch(ModelNotFoundException $ex){
            Log::error($ex);
            session()->flash("error", "Can't find user.");
            return redirect()->back();
        } catch (Exception $ex) {
            Log::error($ex);
            session()->flash("error", "Server Busy. Please Try Again");
            return redirect()->back();
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $rules = [
                'digit.*' => 'required|integer|digits:1',
            ];
            $messages = [
                'digit.*.required' => 'All OTP digits are required.',
                'digit.*.integer' => 'Each OTP digit must be an integer.',
                'digit.*.digits' => 'Each OTP digit must be a single number.',
                'digit.*.size' => 'The OTP must be exactly four digits.',
            ];
            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors(), 422);
            }
            $otp = implode('', $request->digit);
            if (strlen($otp) == 4) {
                $admin = Admin::findOrFail(decrypt($request->id));
                if ($admin->otp == $otp) {
                    $admin->otp = null;
                    $admin->save();
                    session()->flash("success", "Enter the new Password.");
                    return ApiResponse::successResponse(["redirect_url" => route('admin.reset-password-view', ['id' => encrypt($admin->id)])], "Enter the new Password.", 200);
                }
                return ApiResponse::errorResponse([], "OTP not matched try again.", 400);
            }
            return ApiResponse::errorResponse([], "Invaild OTP try again.", 404);
        } catch(ModelNotFoundException $ex){
            return ApiResponse::errorResponse([], "User Not Found", 404);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([], "Server Error.", 500);
        }
    }

    public function resetPasswordView($id)
    {
        try {
            $id = decrypt($id);
            $user = Admin::findOrFail($id);
            if (!empty($user)) {
                return view('Admin.auth.reset-password', compact('user'));
            }
            session()->flash("error", "Can't find restaurant.");
            return redirect()->route('admin.login');
        } catch(DecryptException $ex){
            Log::error($ex);
            session()->flash("error", "Invalid Id.");
            return redirect()->back();
        } catch (Exception $ex) {
            session()->flash("error", "Server Busy. Please Try Again");
            return redirect()->route('admin.login');
        }
    }

    public function resetPassword(Request $request)
    {

        try {
            $rules = [
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
                // session()->flash("error", "Validation Error.");
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $admin = Admin::findOrFail(decrypt($request->id));

            if (!empty($admin)) {
                $admin->password = $request->confirm_password;
                if ($admin->save()) {
                    session()->flash("success", "Password reset sucessully");
                    return redirect()->route('admin.login');
                }
                session()->flash("error", "Password cant be reset.Please try again");
                return redirect()->back();
            }

            session()->flash("error", "Can't find restaturant.");
            return redirect()->route('admin.login');
        } catch (Exception $ex) {
            session()->flash("error", "Server Error.");
            return redirect()->route('admin.login');
        }
    }

    public function contactUs(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/',
            'phone' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
        ], [
            'name.required' => 'Please enter your name.',
            'name.string' => 'Your name must contain only letters, numbers, and spaces.',
            'name.max' => 'Your name should not exceed 255 characters.',
        
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please provide a valid email address.',
            'email.regex' => 'Please provide a valid email address (e.g., example@domain.com).',
        
            'phone.string' => 'Please enter a valid phone number.',
            'phone.max' => 'Phone number should not exceed 255 characters.',
        
            'subject.string' => 'Please enter a valid subject.',
            'subject.max' => 'Subject should not exceed 255 characters.',
        
            'description.required' => 'Please enter a message.',
            'description.string' => 'Your message should be in text format.',
            'description.max' => 'Your message should not exceed 1000 characters.',
        ]);
        
        if ($validator->fails()) {
            
            session()->flash("warning", "Validation Error");
            return redirect()->back()->withErrors($validator)->withInput();
        }
        session()->flash("success", 'Thank you for contacting us! We have received your message and will get back to you as soon as possible.');
        return redirect()->route('admin.contact-us');
        
    }

}
