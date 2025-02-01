<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Packages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
class AdminParcelController extends Controller
{
    public function getParcels(Request $request){
        $validator = Validator::make($request->all(), [
            'status' => 'in:1,2,3,4,5'
        ]);
        if ($validator->fails()) {
            session()->flash("error","Invalid status.");
            return  redirect()->route("admin.get-parcel-list");
        }
        $search = '';
        $status = 1;
        if(isset($request->status) && !empty(isset($request->status))){
            $status = $request->status;
        }
        if(isset($request->search) && !empty(isset($request->search))){
            $search = $request->search;
        }
        $packages = Packages::with("senderDetails","reciverDetails","packaageLoadedTrailer.trip")->where("status", $status)
                ->whereNotNull("sender_details_id")
                ->whereNotNull("reciver_details_id")
                ->when($search, function ($query, $search) {
                    $query->where('reference_number', 'like', '%' . $search . '%');
                })
                ->orderBy('created_at', 'DESC')
                ->paginate(10);
        return view("Admin.parcel.index",compact("packages","status","search"));   
    }

    public function addParcels(Request $request){
        return view("Admin.parcel.add");   
    }

    public function editParcels(Request $request){
        return view("Admin.parcel.add");   
    }

    public function viewParcels(Request $request){
        try{
            $package = Packages::with("senderDetails","reciverDetails")->findOrFail(decrypt($request->package_id));
            $data = [
                'senderName' => $package->senderDetails->name,
                'senderAddress' => $package->senderDetails->address,
                'receiverName' => $package->reciverDetails->name,
                'receiverAddress' => $package->reciverDetails->address,
                'referenceNumber' => $package->reference_number,
                'qrCode' => asset('Admin/images/qr-code.jpg'), 
            ];
             $html = View::make('pages.slip', $data)->render();
             $options = new Options();
             $options->set('isHtml5ParserEnabled', true);
             $options->set('isRemoteEnabled', true);
             $dompdf = new Dompdf($options);
             $dompdf->loadHtml($html);
             $dompdf->setPaper('A4', 'portrait');
             $dompdf->render();
             $fileName = 'slip_' . $package->id . '.pdf';
             $filePath = 'slips/' . $fileName;
             Storage::disk('public')->put($filePath, $dompdf->output());
             $fileUrl = asset(Storage::url($filePath));
            return view("Admin.parcel.view", compact("package","fileUrl"));  
        } catch(DecryptException $ex){
            Log::error($ex);
            session()->flash("error", "Invalid Id.");
            return redirect()->back();
        } catch(Exception $ex){
            Log::error($ex);
            session()->flash("error", "Server Error.");
            return redirect()->back();
        } 
    }

    public function markComplete(Request $request){
        $package = Packages::findOrFail($request->package_id);
        $package->status = 5;
        $package->save();
        return ApiResponse::successResponse(null, "Package Delivered Successfully.",200);
    }

}
