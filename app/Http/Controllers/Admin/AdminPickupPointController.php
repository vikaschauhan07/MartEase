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
    public function index(Request $request) {
        $search = '';
        $tab = 1;
        if(isset($request->search) && !empty(isset($request->search))){
            $search = $request->search;
        }
        if(isset($request->tab) && !empty(isset($request->tab))){
            $tab = $request->tab;
        }
        $city = "Calgary";
        if($tab == 2){
            $city = "Edmonton";
        }
        $pickuppoints = Pickuppoints::where("city", $city)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('address', 'like', '%' . $search . '%')
                        ->orWhere('buisness_name', 'like', '%' . $search . '%');
                });
            })
        ->orderBy("created_at", "DESC")
        ->paginate(10);
        return view("Admin.pickup-point.index", compact("pickuppoints","search", "tab"));        
    }

    public function add(){
        $cityArray = ProjectConstants::CITY_ARRAY;
        return view("Admin.pickup-point.add", compact("cityArray"));        
    }

    public function addpickupPoint(Request $request){
        $validator = Validator::make($request->all(), [
            "buisness_name" => "required|min:5|max:55",
            "address" => "required|min:5|max:255",
            "phone_number" => 'required|numeric|digits_between:8,15|unique:pickup_points,phone_number',
            "city" => "required|numeric|min:1",
        ],[
            "city.required" => "Select city.",
            "city.min" => "Select city."
        ]);
        if ($validator->fails()) {
            return ApiResponse::validationResponse($validator->errors(), ProjectConstants::VALIDATION_ERROR);
        }
        $cityArray = ProjectConstants::CITY_ARRAY;
        $pickupPoints = new Pickuppoints();
        $pickupPoints->buisness_name = $request->buisness_name;
        $pickupPoints->address = $request->address;
        $pickupPoints->phone_number = $request->phone_number;
        $pickupPoints->city = $cityArray[$request->city];
        $pickupPoints->save();
        $response = [
            "redirect_url" => route("admin.get-pickup-points")
        ];
        session()->flash("success", "Pick point added sucessfully.");
        return ApiResponse::successResponse($response, "Pick point added sucessfully.", ProjectConstants::SUCCESS);
    }

    public function removePickup(Request $request){
        $loadTrailer =  Pickuppoints::findOrFail($request->pick_up_id);
        $loadTrailer->delete();
        session()->flash("success", "Pickup point removed successfully.");
        return redirect()->back();
    }
    
}
