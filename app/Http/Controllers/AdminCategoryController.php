<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Models\BlogFiles;
use App\Models\Categorys;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class AdminCategoryController extends Controller
{
    public function getAllCategorys(Request $request){
        $status = 1;
        $isRequested = $request->isRequested ?? 0;
        $search = '';
        $categorys = Categorys::where("is_admin_approved", 1)->paginate(10);
        if(isset($request->isRequested) && $request->isRequested == 1){
            $categorys = Categorys::where("is_admin_approved", 0)->paginate(10);
            return view("Admin.category.index",compact("status","categorys","search","isRequested"));
        }
        return view("Admin.category.index",compact("status","categorys","search","isRequested"));
    }

    public function addCategory(Request $request){
        return view("Admin.category.add");
    }

    
    public function addCategoryPost(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|min:3', 
                'category_image' => 'nullable|file|mimes:jpg,jpeg,png|max:4096', 
                'description' => 'required|string|min:10|max:255'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors(), ProjectConstants::VALIDATION_ERROR);
            }
            $category = new Categorys();
            $message = "Category Added Sucessfully.";
            if(isset($request->category_id) && !empty($request->category_id)){
                $category = Categorys::findOrFail($request->category_id);
                $message = "Category Updated Sucessfully.";
            }
            $category->name = $request->name;
            if ($request->has('category_image') && !empty($request->file('category_image'))) {
                $uploadedFile = $request->file('category_image');
                // foreach ($request->file('category_image') as $uploadedFile) {
                    $category->image = AwsHelper::uploadFile($uploadedFile, ProjectConstants::BLOG_FILE);
                // }
            }
            $category->is_admin_approved = 1;
            $category->description = $request->description;
            $category->save();
            $response = [
                "redirect_url" => route("admin.view-category",["category_id" => encrypt($category->id)])
            ];
            session()->flash("success", $message);
            return ApiResponse::successResponse($response , $message,200);
        } catch(ModelNotFoundException $ex){
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Blog Not Found.", 404);
        } catch(Exception $ex){
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", 500);
        }
    }
    
    public function viewCategoryDetails(Request $request){
        $category = Categorys::findorFail(decrypt($request->category_id));
        return view("Admin.category.view", compact("category"));
    }

    public function editCategory(Request $request){
        $category = Categorys::findorFail(decrypt($request->category_id));
        return view("Admin.category.edit", compact("category"));
    }

    public function getAllCategorysApi(Request $request){
        $categorys = Categorys::where("is_admin_approved", 1)->select('id', 'name', 'description', 'image')->paginate(10);
    
        $categorys->getCollection()->transform(function ($category) {
            $category->image = asset($category->image);
            return $category;
        });
    
        return ApiResponse::successResponse($categorys, "Category got successfully.", 200);
    }

    
    public function requestCategory(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|min:3', 
                'category_image' => 'nullable|file|mimes:jpg,jpeg,png|max:4096', 
                'description' => 'required|string|min:10|max:255'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $category = new Categorys();
            $message = "Category Added Sucessfully.";
            if(isset($request->category_id) && !empty($request->category_id)){
                $category = Categorys::findOrFail($request->category_id);
                $message = "Category Updated Sucessfully.";
            }
            $category->name = $request->name;
            if ($request->has('category_image') && !empty($request->file('category_image'))) {
                $uploadedFile = $request->file('category_image');
                // foreach ($request->file('category_image') as $uploadedFile) {
                    $category->image = AwsHelper::uploadFile($uploadedFile, ProjectConstants::BLOG_FILE);
                // }
            }
            $category->is_requested = 1;
            $category->is_requested = $user->id;
            $category->description = $request->description;
            $category->save();
            $response = [
                "redirect_url" => route("admin.view-category",["category_id" => encrypt($category->id)])
            ];
            session()->flash("success", $message);
            return ApiResponse::successResponse(null , $message,200);
        } catch(ModelNotFoundException $ex){
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Blog Not Found.", 404);
        } catch(Exception $ex){
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", 500);
        }
    }
}
