<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\TrailerLoad;
use App\Models\Trailers;
use App\Models\Packages;
use Aws\finspace\finspaceClient;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;


class AdminTrailerController extends Controller
{
    public function index() {
        $trailers = Trailers::get();
        return view("Admin.trailer.index",compact("trailers"));        
    }

    public function view(Request $request){
        try{
            $trailer = Trailers::findOrFail(decrypt($request->trailer_id));
            $trailers = Trailers::get();
            $trailerLoads = TrailerLoad::where("trailer_id", $trailer->id)->where("is_erased", 0)->paginate(10);
            return view("Admin.trailer.view", compact("trailer", "trailers","trailerLoads"));        
        } catch(DecryptException $ec) {
            session()->flash("error", "Invalid Id.");
            return redirect()->back();
        }  catch(ModelNotFoundException $ec) {
            session()->flash("success", "Trailer not found.");
            return redirect()->back();
        }  catch(Exception $ec) {
            session()->flash("success", "Server Error.");
            return redirect()->back();
        }
    }

    public function getDeatails(Request $request){
        $trailer = Trailers::with("trailerLoad")->findOrFail($request->trailer_id);
        $response = [
            "trailer" => $trailer,
            "trailerLoadCount" => $trailer->trailerLoad->count()
        ];
        return ApiResponse::successResponse($response, "Details Got Sucess", ProjectConstants::SUCCESS); 
    }

    public function lockTrailer(Request $request){
        $trailer = Trailers::findOrFail($request->trailer_id);
        if($trailer->is_locked == 1){
            $trailer->is_locked = 0;
        } else {
            $trailer->is_locked = 1;
        }
        $trailer->save();
        return ApiResponse::successResponse(null, "Trailer Updated Sucess.", ProjectConstants::SUCCESS); 
    }

    public function removeParcel(Request $request){
        $loadTrailer =  TrailerLoad::findOrFail($request->trailer_load_id);
        $package = Packages::findOrFail( $loadTrailer->package_id );
        $package->status = 1;
        $package->save();
        $loadTrailer->forceDelete();
        session()->flash("success", "Parcel removed from the trailer successfully");
        return redirect()->back();
    }

    public function eraseContent(Request $request){
        $trailer = Trailers::findOrFail($request->trailer_id);

        TrailerLoad::where("trailer_id", $trailer->id)->where("status", 1)->where("is_erased", 0)->update(["status" => 2, "is_erased" => 1]);
        $loaededPackages = TrailerLoad::where("trailer_id", $trailer->id)->pluck("package_id")->toArray();        
        Packages::whereIn("id", $loaededPackages)->update(["status" => 3]);
        $trailer->status = 1;
        $trailer->is_locked = 0;
        $trailer->save();
        return ApiResponse::successResponse(null, "Comntent erased successfully", 200);
    }
}
