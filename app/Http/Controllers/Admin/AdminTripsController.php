<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\TrailerLoad;
use App\Models\Trips;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Trailers;
use Illuminate\Support\Facades\Auth;

class AdminTripsController extends Controller
{
    public function getTrips(Request $request){
        $validator = Validator::make($request->all(), [
            'status' => 'in:1,2,3,4'
        ]);
        if ($validator->fails()) {
            session()->flash("error","Invalid status.");
            return  redirect()->route("admin.get-trip-list");
        }
        $status = 1;
        $search = '';
        if(isset($request->status) && !empty(isset($request->status))){
            $status = $request->status;
        }
        if(isset($request->search) && !empty(isset($request->search))){
            $search = $request->search;
        }
        $cityArray = ProjectConstants::CITY_ARRAY;
        $trips = Trips::where("status", $status)
        ->when($search, function ($query, $search) {
            $query->where('trip_number', 'like', '%' . $search . '%');
        })
        ->orderBy("created_at", "DESC")
        ->paginate(10);
        return view("Admin.trip.index",compact("trips","cityArray","status","search"));   
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
        $tripNumber = "TR". now()->timestamp. mt_rand(1000, 9999);
        $status = 1;
        if(isset($request->trip_id) && $request->trip_id){
            $trip = Trips::findOrFail($request->trip_id);
            $message = "Trip updated sucessfully.";
            $tripNumber = $trip->trip_number ?? $tripNumber;
            $status =  $trip->status;
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
        $trip->trip_number = $tripNumber;
        $trip->status = $status;
        $trip->save();

        $trailer = Trailers::where("id", $trip->trailer_number)->first();
        $trailer->status = 2;
        $trailer->save();

        $trailerLoad = TrailerLoad::where("trailer_id", $trailer->id)
                ->where("is_erased", 0)
                ->where("status", 1)
                ->update(["trip_id" => $trip->id]);
        $response = [
            "id" => $trip->id,
            "trip_number" => $trip->trip_number,
            "from_city" => $trip->from_city,
            "to_city" => $trip->to_city,
            "delivery_price" => $trip->delivery_price,
            "pickup_date" => date('l F j, Y', strtotime($trip->pickup_date)),
            "pickup_window" => ProjectConstants::TIME_SLOTS[$trip->pickup_window],
            "dropoff_window" => ProjectConstants::TIME_SLOTS[$trip->dropoff_window],
            "distance" => $trip->distance,
            "trailer_number" => $trip->trailer_number,
            "trailer_length" => $trip->trailer_length,
            "trailer_breadth" => $trip->trailer_breadth,
            "trailer_height" => $trip->trailer_height,
            "trailer_weight" => $trip->trailer_weight,
            "delivery_price" => $trip->delivery_price,
            "pickup_location" => $trip->pickup_location,
            "dropoff_location" => $trip->dropoff_location,
            "status" => $trip->status,
        ];

        $responseFinal = [
            "redirect_link" => route('admin.get-trip-list'),
            "response" => $response
        ];
        session()->flash("success", $message);
        return ApiResponse::successResponse($responseFinal, $message, ProjectConstants::SUCCESS);
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

    public function setSocketId(Request $request){
        $admin = Auth::guard("admin")->user();
        $admin->socket_id = $request->socket_id;
        $admin->save();
        return true;
    }
}
