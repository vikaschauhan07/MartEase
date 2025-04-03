<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Categorys;
use App\Models\ProductImages;
use App\Models\ProductPrices;
use App\Models\Products;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserProductCntroller extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/user/products/request",
     *     summary="Request a new product",
     *     description="Allows a user to request a new product with images and prices",
     *     tags={"Products"},
     *     security={{ "bearerAuth":{} }},
     * 
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description", "category_id", "product_images", "product_prices"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Sample Product"),
     *                 @OA\Property(property="description", type="string", maxLength=500, example="This is a sample product description."),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 
     *                 @OA\Property(
     *                     property="product_prices",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="name", type="string", example="Basic Plan"),
     *                         @OA\Property(property="price", type="number", format="float", example=19.99)
     *                     )
     *                 ),
     * 
     *                 @OA\Property(
     *                     property="product_images",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary")
     *                 )
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Product request submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product requested successfully."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category Not Found."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     )
     * )
     */
    public function requestProduct(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|min:3',
                'description' => 'required|min:5|max:500',
                'category_id' => 'required|integer|exists:categorys,id',
                'product_images' => 'required|array',
                'product_images.*' => 'file|mimes:jpg,jpeg,png|max:4096',
                'product_prices' =>   'required|array',
                'product_prices.*.name' => 'required|string|min:2|max:255',
                'product_prices.*.price' => 'required|numeric|min:1',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }

            $user = Auth::guard('user')->user();
            $category = Categorys::findOrFail($request->category_id);
            $product = new Products();
            $product->name = $request->name;
            $product->description = $request->description;
            $product->category_id = $category->id;
            $product->user_id = $user->id;
            $product->save();

            if ($request->has('product_images') && !empty($request->file('product_images'))) {
                foreach ($request->file('product_images') as $uploadedFile) {
                    $productImages = new ProductImages();
                    $productImages->product_id = $product->id;
                    $productImages->file = AwsHelper::uploadFile($uploadedFile, ProjectConstants::BLOG_FILE);
                    $productImages->save();
                }
            }

            if ($request->has('product_prices') && !empty($request->product_prices)) {
                foreach ($request->product_prices as $price) {
                    $productPrices = new ProductPrices();
                    $productPrices->product_id = $product->id;
                    $productPrices->name = $price['name'];
                    $productPrices->price = $price['price'];
                    $productPrices->save();
                }
            }

            return ApiResponse::successResponse(null, "Product requested successfully.", 200);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Blog Not Found.", 404);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/products",
     *     summary="Get all products",
     *     description="Retrieves a paginated list of all products, optionally filtered by category.",
     *     tags={"Products"},
     *     security={{ "bearerAuth":{} }},
     *
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         required=false,
     *         description="Filter products by category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     * 
     *     @OA\Response(
     *         response=200,
     *         description="List of products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Products fetched successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total_pages", type="integer", example=5),
     *                 @OA\Property(property="total_items", type="integer", example=50),
     *                 @OA\Property(property="products", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Sample Product"),
     *                         @OA\Property(property="description", type="string", example="This is a sample product description."),
     *                         @OA\Property(property="category_id", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Electronics")
     *                         ),
     *                         @OA\Property(property="user_id", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="John Doe")
     *                         ),
     *                         @OA\Property(property="product_images", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=101),
     *                                 @OA\Property(property="file", type="string", format="url", example="https://example.com/product.jpg")
     *                             )
     *                         ),
     *                         @OA\Property(property="product_prices", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="id", type="integer", example=201),
     *                                 @OA\Property(property="name", type="string", example="Basic Plan"),
     *                                 @OA\Property(property="price", type="number", format="float", example=19.99)
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Products not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Products Not Found."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     )
     * )
     */
    public function getAllProducts(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'nullable|integer|exists:categorys,id',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }

            $user = Auth::guard('user')->user();
            $products = Products::orderBy("created_at", "DESC")->paginate(10);
            $productsT = $products->map(function ($product) {
                return [
                    "id" => $product->id,
                    "name" => $product->name,
                    "description" => $product->description,
                    "category_id" => [
                        "id" => $product->category->id,
                        "name" => $product->category->name
                    ],
                    "user_id" => [
                        "id" => $product->user->id,
                        "name" => $product->user->name,

                    ],
                    "product_images" => $product->images->map(function ($image) {
                        return [
                            "id" => $image->id,
                            "file" => asset($image->file)
                        ];
                    }),
                    "product_prices" => $product->prices->map(function ($price) {
                        return [
                            "id" => $price->id,
                            "name" => $price->name,
                            "price" => $price->price
                        ];
                    }),
                ];
            });
            return ApiResponse::successResponse($products->isEmpty() ? null : $products->setCollection($productsT), "Product requested successfully.", 200);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Blog Not Found.", 404);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", 500);
        }
    }
}
