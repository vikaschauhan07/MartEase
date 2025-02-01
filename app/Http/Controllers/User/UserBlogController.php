<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Blogs;
use Illuminate\Http\Request;

class UserBlogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/user/get-all-blogs",
     *     summary="Get all blogs with optional search",
     *     description="Fetches a list of blogs with optional search filter, including related blog files.",
     *     operationId="getAllBlogs",
     *     tags={"Blogs By Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search query to filter blogs by title",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blogs listed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Blogs Listed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="content", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date", example="2025-01-20"),
     *                     @OA\Property(
     *                         property="blog_files",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="file", type="string", format="url", example="http://example.com/path/to/file")
     *                         )
     *                     ),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No blogs found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="No blogs found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function getAllBlogs(Request $request)
    {
        $search = $request->search;
        $blogs = Blogs::with("blogFiles")
            ->when($search, function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%');
                });
            })
            ->where("status", 1)
            ->orderBy("created_at", "DESC")
            ->paginate(10);

        $blogs->setCollection(
            $blogs->map(function ($blog) {
                return [
                    "id" => $blog->id,
                    "title" => $blog->title,
                    "content" => $blog->content,
                    "created_at" => $blog->created_at->format("Y"),
                    "blog_files" => $blog->blogFiles->count() > 0 ? $blog->blogFiles->map(function ($file) {
                        return [
                            "id" => $file->id,
                            "file" => asset($file->file)
                        ];
                    }) : null,
                ];
            })
        );
        if ($blogs->count() < 1) {
            $blogs = null;
        }
        return ApiResponse::successResponse($blogs, "Blogs Listed", 200);
    }
}
