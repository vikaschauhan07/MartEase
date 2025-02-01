<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Trips;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Trailers;

class AdminTripsController extends Controller
{
    public function getTrips(Request $request){
        $status = 1;
        if(isset($request->status) && !empty(isset($request->status))){
            $status = $request->status;
        }
        $cityArray = ProjectConstants::CITY_ARRAY;
        $trips = Trips::where("status", $status)->paginate();
        return view("Admin.trip.index",compact("trips","cityArray","status"));   
    }

    public function addTrips(Request $request){
        $time_slots_array = ProjectConstants::TIME_SLOTS;
        $cityArray = ProjectConstants::CITY_ARRAY;
        $trailers = Trailers::where("is_locked",1)->where("status", 1)->get();
        return view("Admin.trip.add", compact("time_slots_array","cityArray","trailers"));   
    }

    public function addTripsPost(Request $request){
        $validator = Validator::make($request->all(), [
            "trip_id" => "nullable|numeric|min:1",
            "from_city" => "required|numeric|min:1",
            "to_city" => "required|numeric|min:1",
            "trailer_number" => "required|numeric|min:1|max:20",
            "delivery_price" => "required|numeric|min:1",
            "pickup_date" => "required|date_format:d-m-Y",
            "pickup_window" => "required|numeric|min:1|max:25",
            "dropoff_window" =>"required|numeric|min:1|max:25",
            "distance" => "required|numeric|min:1",
            "trailer_length" => "required|numeric|min:1",
            "trailer_breadth" => "required|numeric|min:1",
            "trailer_height" => "required|numeric|min:1",
            "trailer_weight" => "required|numeric|min:1",
            "dropoff_location" => "required|string|min:5|max:255",
            "pickup_location" => "required|string|min:5|max:255",
        ]);
        if ($validator->fails()) {
            return ApiResponse::validationResponse($validator->errors(), 422);
        }
        $message = "Trip Added sucessfully.";
        $cityArray = ProjectConstants::CITY_ARRAY;
        $trip = new Trips();
        if(isset($request->trip_id) && $request->trip_id){
            $trip = Trips::findOrFail($request->trip_id);
            $message = "Trip updated sucessfully.";
        }
        $trip->from_city =  $cityArray[$request->from_city];
        $trip->to_city =  $cityArray[$request->to_city];
        $trip->trailer_number = $request->trailer_number;
        $trip->delivery_price = $request->delivery_price;
        $trip->pickup_date = Carbon::createFromFormat('d-m-Y', $request->pickup_date)->format('Y-m-d');
        $trip->pickup_window = $request->pickup_window;
        $trip->dropoff_window = $request->dropoff_window;
        $trip->distance = $request->distance;
        $trip->trailer_length = $request->trailer_length;
        $trip->trailer_breadth = $request->trailer_breadth;
        $trip->trailer_height = $request->trailer_height;
        $trip->trailer_weight = $request->trailer_weight;
        $trip->pickup_location = $request->pickup_location;
        $trip->dropoff_location = $request->dropoff_location;
        $trip->save();

        $trailer = Trailers::where("id", $trip->trailer_number)->first();
        $trailer->status = 2;
        $trailer->save();
        $response = [
            "redirect_url" => route("admin.get-trip-list")
        ];
        session()->flash("success", $message);
        return ApiResponse::successResponse($response, $message, ProjectConstants::SUCCESS);
    }

    public function editTrips(Request $request){
        $time_slots_array = ProjectConstants::TIME_SLOTS;
        $cityArray = ProjectConstants::CITY_ARRAY;
        $trailers = Trailers::where("is_locked",1)->where("status", 1)->get();
        $trip = Trips::where("id",decrypt($request->trip_id))->first();
        return view("Admin.trip.edit", compact("time_slots_array","cityArray","trailers","trip"));   
    }

    public function viewTrips(Request $request){
        $trip = Trips::findOrFail(decrypt($request->trip_id));
        return view("Admin.trip.view",compact("trip"));   
    }
}
