<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\BlogFiles;
use App\Models\Blogs;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminBlogController extends Controller
{
    public function getBlogs(Request $request){
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:1,2',
            'search' => 'nullable|max:55'
        ]);
        if ($validator->fails()) {
            session()->flash("error", "Please enter valid input.");
            return redirect()->route('admin.get-blog-list');
        }
        $status = $request->status ?? 0;
        $search = $request->search ?? ''; 
        $blogs = Blogs::when($search, function ($query, $search) {
            return $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%');
            });
        })
        ->when($request->status === '2', function ($query) {
            return $query->where('status', 1);
        })
        ->when($request->status === '1', function ($query) {
            return $query->where('status', 0);
        })
        ->orderBy("created_at", "DESC")
        ->paginate(10);
        return view("Admin.blogs.index", compact("blogs","search","status"));
    }

    public function addBlog(Request $request){
        return view("Admin.blogs.add");
    }

    public function uploadFile(Request $request){
        
        if ($request->has('upload_file') && !empty('upload_file')) {
            $uploadedFile = $request->file('upload_file');
            $fileUrl = AwsHelper::uploadFile($uploadedFile, ProjectConstants::BLOG_FILE);
        }

        return ApiResponse::successResponse(["url" => asset($fileUrl)], "asdf", 200);

    }

    public function addBlogPost(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255|min:3',
                'content' => 'required|string|max:50000|min:3', 
                'blog_files' => 'nullable|array', 
                'blog_files.*' => 'file|mimes:jpg,jpeg,png|max:4096',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors(), ProjectConstants::VALIDATION_ERROR);
            }
            $blog = new Blogs();
            $message = "Blog Added Sucessfully.";
            if(isset($request->blog_id) && !empty($request->blog_id)){
                $blog = Blogs::findOrFail($request->blog_id);
                $message = "Blog Updated Sucessfully.";
            }
            $blog->title = $request->title;
            $blog->content = $request->content;
            $blog->save();
            if(isset($request->removedImages) && !empty($request->removedImages) && count($request->removedImages) > 0){
                BlogFiles::whereIn("id", $request->input('removedImages', []))->delete();
            }
            if ($request->has('blog_files') && !empty($request->file('blog_files'))) {
                foreach ($request->file('blog_files') as $uploadedFile) {
                    $userDocumnents = new BlogFiles();
                    $userDocumnents->blog_id = $blog->id;
                    $userDocumnents->file = AwsHelper::uploadFile($uploadedFile, ProjectConstants::BLOG_FILE);
                    $userDocumnents->save();
                }
            }
            $response = [
                "redirect_url" => route("admin.view-blog",["blog_id" => encrypt($blog->id)])
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

    public function viewBlogDetails(Request $request){
        $blog = Blogs::findorFail(decrypt($request->blog_id));
        $blogFiles = BlogFiles::where("blog_id",$blog->id)->get();
        return view("Admin.blogs.view", compact("blog"));
        
    }

    public function editBlog(Request $request){
        $blog = Blogs::findorFail(decrypt($request->blog_id));
        $blogFiles = BlogFiles::where("blog_id",$blog->id)->get();
        return view("Admin.blogs.edit", compact("blog"));
    }

    public function changeBlogStatus(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'blog_id' => 'required|numeric|min:1|exists:blogs,id',
            ]);
            if ($validator->fails()) {
                return  ApiResponse::validationResponse($validator->errors()->toArray(),ProjectConstants::VALIDATION_ERROR);
            }
            $blog = Blogs::findOrFail($request->blog_id);
            $blogStatus = $blog->status;
            
            if($blogStatus == 0) {
                $blog->status = 1;
            } else {
                $blog->status = 0;
            }
            $blog->save();
            return ApiResponse::successResponse([],"Status Changed Sucessfully.", ProjectConstants::SUCCESS);
        } catch(ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([],"Blog Not Found.", ProjectConstants::NOT_FOUND);
        } catch(Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse([],"Server error.", ProjectConstants::SERVER_ERROR);
        }
    }

    public function deleteBlog(Request $request){
        try{
            BlogFiles::where("blog_id", decrypt($request->blog_id))->delete();
            Blogs::where("id", decrypt($request->blog_id))->delete();
            session()->flash("success", "Blog deleted successfully.");
            return redirect()->route("admin.get-blog-list");
        }   catch(Exception $ex){
            Log::error($ex);
            session()->flash("success", "Server Error.");
            return redirect()->back();
        }
    }
}
