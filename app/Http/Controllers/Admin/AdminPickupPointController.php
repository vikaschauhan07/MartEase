<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Pickuppoints;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminPickupPointController extends Controller
{
    //
    public function index() {
        $pickuppoints = Pickuppoints::paginate(10);
        return view("Admin.pickup-point.index", compact("pickuppoints"));        
    }

    public function add(){
        return view("Admin.pickup-point.add");        
    }

    public function addpickupPoint(Request $request){
        $validator = Validator::make($request->all(), [
            "buisness_name" => "required|min:5|max:55",
            "address" => "required|min:5|max:255",
            "phone_number" => 'required|numeric|digits_between:8,15',

        ]);
        if ($validator->fails()) {
            return ApiResponse::validationResponse($validator->errors(), ProjectConstants::VALIDATION_ERROR);
        }
        $pickupPoints = new Pickuppoints();
        $pickupPoints->buisness_name = $request->buisness_name;
        $pickupPoints->address = $request->address;
        $pickupPoints->phone_number = $request->phone_number;
        $pickupPoints->save();
        $response = [
            "redirect_url" => route("admin.get-pickup-points")
        ];
        session()->flash("success", "Pick point added sucessfully.");
        return ApiResponse::successResponse($response, "Pick point added sucessfully.", ProjectConstants::SUCCESS);
    }
}
