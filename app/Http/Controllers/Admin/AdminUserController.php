<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Mail\EmailService;
use App\Models\GetNotifiedEmails;
use App\Models\Packages;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    public function getUsers(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|in:1,2',
                'search' => 'nullable|max:55'
            ]);
            if ($validator->fails()) {
                session()->flash("error", "Please enter valid input.");
                return redirect()->route('admin.get-user-list');
            }
            $status = $request->status ?? 0;
            $search = $request->search ?? ''; 
            $users = User::when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when($request->status === '2', function ($query) {
                return $query->where('status', 1);
            })
            ->when($request->status === '1', function ($query) {
                return $query->where('status', 0);
            })
            ->where("is_email_verified", 1)
            ->orderBy("created_at", "DESC")
            ->paginate(10);
            return view("Admin.users.index", compact("users","search","status"));
        } catch(Exception $ex) {
            Log::error($ex);
            session()->flash("error", "Server Error.");
            return redirect()->back();
        }
    }

    public function getNotifiedEmails(Request $request){
        try{
            $getNotifiedEmails = GetNotifiedEmails::orderBy("created_at", "DESC")->paginate(10);
            return view("Admin.notified-emails.index", compact("getNotifiedEmails"));
        } catch(Exception $ex) {
            Log::error($ex);
            session()->flash("error", "Server Error.");
            return redirect()->back();
        }
    }

    public function getNotified(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email:rfc,dns|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors(), 422);
            }

            $getNotified = GetNotifiedEmails::where("email", $request->email)->first();
            if(!$getNotified){
                $getNotified = new GetNotifiedEmails();
                $getNotified->email = $request->email;
                $getNotified->save();
            }            
            $subject = "Welcome to HUNKR.";
            $name = null;
            $emailOtp = null;
            Mail::to($getNotified->email)->send(new EmailService($subject, $name,$emailOtp, 2));
            return ApiResponse::successResponse(null, 'You have successfully subscribed to receive notifications. Stay tuned for updates!', ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Internal server error.", ProjectConstants::SERVER_ERROR);
        }
    }

    public function addUser(Request $request){
        try{
            return view("Admin.users.add");
        } catch(Exception $ex) {
            Log::error($ex);
            session()->flash("success", "Server Error.");
            return redirect()->back();
        }
    }

    public function viewUserDetails(Request $request){
        try{
            $user = User::findOrFail(decrypt($request->user_id));
            return view("Admin.users.view",compact("user"));
        } catch(DecryptException $ex) {
            Log::error($ex);
            session()->flash("error", "Invalid id.");
            return redirect()->back();
        } catch(Exception $ex) {
            Log::error($ex);
            session()->flash("error", "Server Error.");
            return redirect()->back();
        }
    }

    public function changeUserStatus(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric|min:1|exists:users,id',
            ]);
            if ($validator->fails()) {
                return  ApiResponse::validationResponse($validator->errors()->toArray(),ProjectConstants::VALIDATION_ERROR);
            }
            $user = User::findOrFail($request->user_id);
            $userStatus = $user->status;
            
            if($userStatus == 0) {
                $user->status = 1;
            } else {
                $user->tokens()->delete();
                $user->status = 0;
            }
            $user->save();
            return ApiResponse::successResponse([],"Status Changed Sucessfully.", ProjectConstants::SUCCESS);
        } catch(ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([],"User Not Found.", ProjectConstants::NOT_FOUND);
        } catch(Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([],"Server error.", ProjectConstants::SERVER_ERROR);
        }
    }
    
}
