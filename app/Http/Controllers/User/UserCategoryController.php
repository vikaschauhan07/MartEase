<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Categorys;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserCategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user/categorys",
     *     summary="Get all approved categories",
     *     description="Fetches a paginated list of categories that are approved by admin.",
     *     tags={"Category"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of approved categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category got successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Electronics"),
     *                         @OA\Property(property="description", type="string", example="All electronic gadgets"),
     *                         @OA\Property(property="image", type="string", example="https://example.com/uploads/category1.jpg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function getAllCategorysApi(Request $request)
    {
        $categorys = Categorys::where("is_admin_approved", 1)
            ->when($request->has('search') && !empty($request->search), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
                });
            })
            ->select('id', 'name', 'description', 'image')
        ->paginate(10);

        $categorys->getCollection()->transform(function ($category) {
            $category->image = asset($category->image);
            return $category;
        });

        return ApiResponse::successResponse($categorys->isEmpty() ? null : $categorys , "Category got successfully.", 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/categorys/request",
     *     summary="Create or update a category",
     *     description="This API allows users to request a new category or update an existing one.",
     *     tags={"Category"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string", example="Electronics", description="Category name"),
     *             @OA\Property(property="category_image", type="string", format="binary", description="Category image (JPG, JPEG, PNG)"),
     *             @OA\Property(property="description", type="string", example="All electronic gadgets", description="Category description"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="Category ID for updating an existing category")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category Added/Updated Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category Added Successfully."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Blog Not Found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function requestCategory(Request $request)
    {
        try {
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
            if (isset($request->category_id) && !empty($request->category_id)) {
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
            $category->created_by = $user->id;
            $category->description = $request->description;
            $category->save();
            return ApiResponse::successResponse(null, $message, 200);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Blog Not Found.", 404);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", 500);
        }
    }
}
