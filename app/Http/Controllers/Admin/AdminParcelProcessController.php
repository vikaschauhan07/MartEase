<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Packages;
use App\Models\TrailerLoad;
use App\Models\Trailers;
use Illuminate\Http\Request;

class AdminParcelProcessController extends Controller
{
    public function index(Request $request){
        return view("Admin.parcel-process.index");   
    }

    public function searchParcel(Request $request){
        if(!isset($request->reference_number) || !$request->reference_number){
            return ApiResponse::errorResponse([],"Enter reference number.", ProjectConstants::NOT_FOUND);
        }
        $parcel = Packages::where("reference_number", $request->reference_number)->first();
        if(!$parcel){
            return ApiResponse::errorResponse([],"Parcel not found with this reference number.", ProjectConstants::NOT_FOUND);
        }
        if($parcel->status > 1){
            return ApiResponse::errorResponse([],"Parcel is already processed.", ProjectConstants::NOT_FOUND);
        }
        $response = [
            "redirect_url" => route("admin.view-parcel-process",["package_id" => encrypt($parcel->id)])
        ];
        return ApiResponse::successResponse($response, "Pick point added sucessfully.", ProjectConstants::SUCCESS);
    }

    public function view(Request $request){
        $package = Packages::with("packageImages","senderDetails","reciverDetails")->findOrFail(decrypt($request->package_id));
        $trailers = Trailers::where("is_locked", 0)->get();
        $assignTrailer = null;
        if(isset($request->trailer_id)){
            $assignTrailer = Trailers::findOrFail($request->trailer_id);
        }
        return view("Admin.parcel-process.view",compact("package","trailers","assignTrailer"));   
    }

    public function addParcelToTrailer(Request $request){
        $package = Packages::findOrFail($request->package_id);
        $trailer = Trailers::findOrFail($request->trailer_id);
        $loadTrailer = new TrailerLoad();
        $loadTrailer->package_id = $package->id;
        $loadTrailer->trailer_id = $trailer->id;
        $loadTrailer->save();
        $package->status = 2;
        $package->save();
        session()->flash("success", "Package assigned succesffluuy");
        return redirect()->route("admin.view-parcel-process",["package_id" => encrypt($package->id) ,"trailer_id" => $trailer->id]);        
    }
}
