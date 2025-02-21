<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Sellers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSwitchUserCntroller extends Controller
{
    public function switchToSeller(Request $request){
        $user  = Auth::guard("user")->user();
        if($user){
            $seller = Sellers::where("id", $user->id)->first();
            $token = $seller->createToken('NewLoginToken')->plainTextToken;
            $sellerArray = [
                "id" => $seller->id,
                "name" => $seller->name,
                "profile_image" => $seller->profile_image ? asset($seller->profile_image) : null,
                "phone_number" => $seller->phone_number,
                "email" => $seller->email,
                "is_user" => 2, 
                "is_phone_verified" => $seller->is_phone_verified,
                "is_email_verified" => $seller->is_email_verified,
                "is_password_set" => $seller->is_password_set 
            ];
            $response = ["user" => $sellerArray, "access_token" => $token];
            return ApiResponse::successResponse($response, "User Switched To Seller Successfully", ProjectConstants::SUCCESS);
        }
    }
}
