<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Packages;
use Illuminate\Http\Request;

class AdminParcelController extends Controller
{
    public function getParcels(Request $request){
        $status = 1;
        if(isset($request->status) && !empty(isset($request->status))){
            $status = $request->status;
        }
        $packages = Packages::with("senderDetails","reciverDetails")->where("status", $status)
                ->whereNotNull("sender_details_id")
                ->whereNotNull("reciver_details_id")
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        return view("Admin.parcel.index",compact("packages","status"));   
    }

    public function addParcels(Request $request){
        return view("Admin.parcel.add");   
    }

    public function editParcels(Request $request){
        return view("Admin.parcel.add");   
    }

    public function viewParcels(Request $request){
        $package = Packages::findOrFail(decrypt($request->package_id));
        return view("Admin.parcel.view", compact("package"));   
    }
}
