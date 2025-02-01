<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\Comments;
use App\Models\Community;
use App\Models\Likes;
use App\Models\PostFiles;
use App\Models\Posts;
use App\Models\Reasons;
use App\Models\Reports;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserPostController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v1/user/get-issues-list",
     *     summary="Get list of report issues",
     *     description="Retrieve a predefined list of report issues.",
     *     security={{"bearerAuth": {}}},
     *     tags={"Issues List To Report In Post"},
     *     @OA\Response(
     *         response=200,
     *         description="Issues List Get Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Issues List Get Successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Downed Tree")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=200
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong."),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function getReportIssuesList(Request $request)
    {
        $response = [
            [
                "id" => 1,
                "name" => "Downed Tree"
            ],
            [
                "id" => 2,
                "name" => "No WIFI"
            ],
            [
                "id" => 3,
                "name" => "No Cellular"
            ],
            [
                "id" => 4,
                "name" => "No Power"
            ],
            [
                "id" => 5,
                "name" => "Down Power Line"
            ],
            [
                "id" => 6,
                "name" => "Flooding"
            ],
            [
                "id" => 7,
                "name" => "Other"
            ]
        ];

        return ApiResponse::successResponse($response, "Issues List Get Successfully", ProjectConstants::SUCCESS);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/create-post",
     *     summary="Create a new post",
     *     description="Create a new post with optional files and associate it with a community based on pin code and city.",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"pin_code", "city"},
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     nullable=true,
     *                     maxLength=280,
     *                     description="Description of the post",
     *                     example="This is a sample description"
     *                 ),
     *                 @OA\Property(
     *                     property="pin_code",
     *                     type="string",
     *                     description="The pin code for the community",
     *                     example="123456"
     *                 ),
     *                 @OA\Property(
     *                     property="city",
     *                     type="string",
     *                     description="The city for the community",
     *                     example="New York"
     *                 ),
     *                 @OA\Property(
     *                     property="issues",
     *                     type="string",
     *                     nullable=true,
     *                     description="Optional issue related to the post",
     *                     example="[{'id': 1,'name': 'Downed Tree'},{'id': 2,'name': 'No WIFI'},{'id': 3,'name': 'No Cellular'}]"
     *                 ),
     *                 @OA\Property(
     *                     property="post_files",
     *                     type="array",
     *                     description="Array of files (images or videos). Maximum 10 files allowed.",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post Added Successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post Added Successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="code", type="integer", example=422)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function createPost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'nullable|max:280',
                "pin_code" => 'required',
                "city" => 'required',
                'issues' => 'nullable',
                'post_files' => 'nullable|array|max:10',
                'post_files.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,bmp,mov,m4v,mp4,mkv,mp4s,wmv'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            DB::beginTransaction();
            $post = new Posts();
            $post->description = $request->description;
            $post->pin_code = ($request->pin_code) ? $request->pin_code : null;
            $post->city = ($request->city) ? $request->city : null;
            $post->user_id = $user->id;
            if (!empty($request->issues) && isset($request->issues)) {
                $post->issues = $request->issues;
                $post->type = 2;
            }
            $post->save();
            if ($request->has('post_files') && !empty($request->file('post_files'))) {
                foreach ($request->file('post_files') as $uploadedFile) {
                    $userDocumnents = new PostFiles();
                    $userDocumnents->post_id = $post->id;
                    $userDocumnents->file = AwsHelper::uploadFile($uploadedFile, ProjectConstants::POST_FILE);
                    $userDocumnents->save();
                }
            }
            $community = Community::where("pin_code", $post->pin_code)->where("city", $request->city)->first();
            if (!$community) {
                $community = new Community();
                $community->city = $post->city;
                $community->pin_code = $post->pin_code;
                $community->save();
            }
            DB::commit();
            return ApiResponse::successResponse(null, "Post Added Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/get-community-list",
     *     summary="Get a list of communities grouped by pin code",
     *     description="Fetches communities grouped by pin code with city information, number of posts, visits, and total cities per pin code.",
     *     tags={"Communities"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number for pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1,
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="The city name",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="New York"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community List Grouped By Pin Code",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Community List Grouped By Pin Code"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="total", type="integer", example=20),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="pin_code", type="string", example="123456"),
     *                         @OA\Property(
     *                             property="city",
     *                             type="array",
     *                             @OA\Items(type="string", example="New York")
     *                         ),
     *                         @OA\Property(property="visits", type="integer", example=0),
     *                         @OA\Property(property="posts", type="integer", example=15),
     *                         @OA\Property(property="total_city", type="integer", example=5)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function getCommunityList(Request $request)
    {
        try {
            $page = request('page', 1);
            $perPage = 10;
            $user = Auth::guard("user")->user();
            $citySearch = $request->input('search', null);
            $reportedPost = Reports::where("model_type", Posts::class)->where("user_id", $user->id)->pluck("model_id")->toArray();
            $communities = Community::select('pin_code', 'city')
                ->when($citySearch, function ($query, $citySearch) {
                    return $query->where('city', 'like', '%' . $citySearch . '%')
                         ->orWhere('pin_code', 'like', '%' . $citySearch . '%');
                })
                ->get()
                ->groupBy('pin_code')
                ->map(function ($group) use ($reportedPost) {
                    $uniqueCities = $group->pluck('city')->unique();
                    $postCount = Posts::where("pin_code", $group->first()->pin_code)->whereNotIn("id", $reportedPost)->count();
                    if($postCount < 1){
                        return null;
                    }
                    return [
                        'pin_code' => $group->first()->pin_code,
                        'city' => $uniqueCities->slice(0, 2)->values(),
                        'visits' => 0,
                        'posts' => $postCount,
                        'total_city' => $uniqueCities->count(),
                    ];
                })
                ->filter()
                ->values();
            $total = $communities->count();
            $paginatedCommunities = new LengthAwarePaginator(
                $communities->forPage($page, $perPage),
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            return ApiResponse::successResponse($paginatedCommunities->count() > 0 ? $paginatedCommunities : null, "Community List Grouped By Pin Code", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/get-city-list",
     *     summary="Fetch cities based on pin code",
     *     description="This endpoint retrieves the list of cities associated with a given pin code.",
     *     tags={"Communities"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="pin_code",
     *         in="query",
     *         description="The page number for pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             default=1,
     *             example=189898
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="City List Get Successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="City List Get Successfully"),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="city", type="string", example="New York")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No cities found for the provided pin code",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="No cities found."),
     *             @OA\Property(property="code", type="integer", example=404),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function getCity(Request $request)
    {
        $city = Community::select('id', 'city')->where("pin_code", $request->pin_code)->get();
        return ApiResponse::successResponse($city->count() > 0 ? $city : null, "City List Get Successfully", ProjectConstants::SUCCESS);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/get-community-post",
     *     summary="Get Community Posts by Pin Code",
     *     description="Fetch posts for the community filtered by pin code and excluding posts reported by the user.",
     *     operationId="getCommunityPost",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pin_code",
     *         in="query",
     *         required=true,
     *         description="The pin code to filter the posts by.",
     *         @OA\Schema(
     *             type="string",
     *             example="123456"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Community posts fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Community List Grouped By Pin Code"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="description", type="string", example="Post description goes here."),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-20T12:00:00Z"),
     *                     @OA\Property(property="type", type="string", example="text"),
     *                     @OA\Property(
     *                         property="like",
     *                         type="object",
     *                         @OA\Property(property="count", type="integer", example=10),
     *                         @OA\Property(property="is_liked", type="boolean", example=true),
     *                         @OA\Property(
     *                             property="recent_like",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             @OA\Property(property="profile_image", type="string", example="https://example.com/profile_image.jpg")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="comment",
     *                         type="object",
     *                         @OA\Property(property="count", type="integer", example=5)
     *                     ),
     *                     @OA\Property(
     *                         property="created_by",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=3),
     *                         @OA\Property(property="name", type="string", example="Jane Smith"),
     *                         @OA\Property(property="profile_image", type="string", example="https://example.com/profile_image.jpg")
     *                     ),
     *                     @OA\Property(property="issues", type="string", example="Some issues related to the post."),
     *                     @OA\Property(
     *                         property="post_files",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="file", type="string", example="https://example.com/file.jpg")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request: Missing or invalid pin code.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid or missing pin code.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server Error.")
     *         )
     *     )
     * )
     */
    public function getCommunityPost(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $reportedPost = Reports::where("model_type", Posts::class)->where("user_id", $user->id)->pluck("model_id")->toArray();
            $posts = Posts::where("pin_code", $request->pin_code)->whereNotIn("id", $reportedPost)->orderBy("created_at", "DESC")->paginate(10);
            $postsT =  $posts->map(function ($post) use ($user) {
                $postFiles = null;
                $recenctLike = null;
                $likeCount = $post->likes->count();
                $isLiked = $post->likes->where("user_id", $user->id)->first() ? true : false;
                $commentCount = $post->comments->count();
                if ($likeCount > 0) {
                    $recentUser = $post->likes()->orderBy("created_at", "DESC")->first()->user;
                    if ($recentUser) {
                        $recenctLike = [
                            "id" => $recentUser->id,
                            "name" => $recentUser->name,
                            "profile_image" => $recentUser->profile_image ? asset($recentUser->profile_image) : null,
                        ];
                    }
                }
                if ($post->postFiles->count() > 0) {
                    $postFiles = $post->postFiles->map(function ($file) {
                        return [
                            "id" => $file->id,
                            "file" => $file->file ? asset($file->file) : null
                        ];
                    });
                }
                return [
                    "id" => $post->id,
                    "description" => $post->description,
                    "created_at" => $post->created_at->setTimeZone($user->time_zone ?? 'UTC'),
                    "type" => $post->type,
                    "like" => [
                        "count" => $likeCount,
                        "is_liked" =>  $isLiked,
                        "recent_like" => $recenctLike
                    ],
                    "commnet" => [
                        "count" => $commentCount
                    ],
                    "created_by" => [
                        "id" => $post->user->id,
                        "name" => $post->user->name,
                        "profile_image" => $post->user->profile_image ? asset($post->user->profile_image) : null,
                    ],
                    "issues" => $post->issues ? ($post->issues) : null,
                    "post_files" => $postFiles
                ];
            });
            return ApiResponse::successResponse($posts->count() > 0 ?  $posts->setCollection($postsT) : null, "Community List Grouped By Pin Code", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/post/details",
     *     summary="Get post details by ID",
     *     description="Retrieve the details of a specific post by its ID, if the post is not reported by the user.",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="query",
     *         description="Post ID to fetch details",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post details retrieved successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Post reported by the user",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Post has been reported by you."),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function getPostById(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $post = Posts::findOrFail($request->post_id);
            if (Reports::where('type', 1)->where('user_id', $user->id)->where(['model_id' => $post->id, 'model_type' => Posts::class])->exists()) {
                return ApiResponse::errorResponse(null, "Post has been reported by you.", ProjectConstants::BAD_REQUEST);
            }
            $postFiles = null;
            $recenctLike = null;
            $likeCount = $post->likes->count();
            $isLiked = $post->likes->where("user_id", $user->id)->first() ? true : false;
            $commentCount = $post->comments->count();
            if ($likeCount > 0) {
                $recentUser = $post->likes()->orderBy("created_at", "DESC")->first()->user;
                if ($recentUser) {
                    $recenctLike = [
                        "id" => $recentUser->id,
                        "name" => $recentUser->name,
                        "profile_image" => $recentUser->profile_image ? asset($recentUser->profile_image) : null,
                    ];
                }
            }
            if ($post->postFiles->count() > 0) {
                $postFiles = $post->postFiles->map(function ($file) {
                    return [
                        "id" => $file->id,
                        "file" => $file->file ? asset($file->file) : null
                    ];
                });
            }
            $postResponse = [
                "id" => $post->id,
                "description" => $post->description,
                "created_at" => $post->created_at->setTimeZone($user->time_zone ?? 'UTC'),
                "type" => $post->type,
                "like" => [
                    "count" => $likeCount,
                    "is_liked" =>  $isLiked,
                    "recent_like" => $recenctLike
                ],
                "commnet" => [
                    "count" => $commentCount
                ],
                "created_by" => [
                    "id" => $post->user->id,
                    "name" => $post->user->name,
                    "profile_image" => $post->user->profile_image ? asset($post->user->profile_image) : null,
                ],
                "issues" => $post->issues ? ($post->issues) : null,
                "post_files" => $postFiles
            ];
            return ApiResponse::successResponse($postResponse, "Community List Grouped By Pin Code", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/post/like",
     *     summary="Like or unlike a post",
     *     description="This endpoint allows a user to like or unlike a post. If the post is already liked, it will be unliked.",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="query",
     *         description="ID of the post to like/unlike",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post liked or unliked successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Post Liked Successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error."),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function likePost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|numeric|exists:posts,id',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $post = Posts::findOrFail($request->post_id);
            $like = Likes::where("model_type", Posts::class)->where("model_id", $post->id)->where("user_id", $user->id)->first();
            if ($like) {
                $like->delete();
                return ApiResponse::successResponse(null, "Post Removed From Likes Successfully.", ProjectConstants::SUCCESS);
            }
            $like = new Likes();
            $like->model_id = $post->id;
            $like->model_type = Posts::class;
            $like->user_id = $user->id;
            $like->save();
            return ApiResponse::successResponse(null, "Post Liked Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/post/comment",
     *     summary="Add a comment to a post",
     *     description="This endpoint allows a user to add a comment to a post. Optionally, a reply to an existing comment can be provided.",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody (
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"post_id", "comment"},
     *             @OA\Property(property="post_id", type="integer", example=1, description="ID of the post to report."),
     *             @OA\Property(property="comment", type="string", description="The comment text", example="Great post!"),
     *             @OA\Property(property="comment_id", type="integer", description="ID of the parent comment if replying to a comment (optional)", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comment Added Successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="object", 
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="comment", type="string", example="Great post!"),
     *                 @OA\Property(property="created_at", type="string", example="2025-01-20T12:00:00Z"),
     *                 @OA\Property(property="like", type="object",
     *                     @OA\Property(property="count", type="integer", example=0),
     *                     @OA\Property(property="is_liked", type="boolean", example=false),
     *                     @OA\Property(property="recent_like", type="null")
     *                 ),
     *                 @OA\Property(property="reply", type="object",
     *                     @OA\Property(property="count", type="integer", example=0),
     *                     @OA\Property(property="reply", type="null")
     *                 ),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="profile_image", type="string", example="http://example.com/image.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error."),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function commentPost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|numeric|exists:posts,id',
                'comment' => 'required|string|max:255',
                'comment_id' => 'nullable|numeric|exists:comments,id'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $post = Posts::findOrFail($request->post_id);
            $comment = new Comments();
            $comment->post_id = $post->id;
            $comment->comment = $request->comment;
            $comment->comment_id = !empty($request->comment_id) ? $request->comment_id : null;
            $comment->user_id = $user->id;
            $comment->save();
            $response = [
                "id" => $comment->id,
                "comment" => $comment->comment,
                "created_at" => $comment->created_at->setTimeZone($user->time_zone ?? 'UTC'),
                "like" => [
                    "count" => 0,
                    "is_liked" =>  false,
                    "recent_like" => null
                ],
                "reply" => [
                    "count" => 0,
                    "reply" => null
                ],
                "user" => [
                    "id" => $user->id,
                    "name" => $user->name,
                    "profile_image" => $user->profile_image ? asset($user->profile_image) : null
                ]
            ];
            return ApiResponse::successResponse($response, "Commnet Added Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/post/comment",
     *     summary="Get all comments for a specific post",
     *     description="This endpoint retrieves all comments and their replies for a given post, including like and reply counts.",
     *     tags={"Posts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="post_id",
     *         in="query",
     *         description="ID of the post to fetch comments for",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of comments and their replies",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Comments fetched successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="comment", type="string", example="Great post!"),
     *                     @OA\Property(property="created_at", type="string", example="2025-01-20T12:00:00Z"),
     *                     @OA\Property(property="like", type="object",
     *                         @OA\Property(property="count", type="integer", example=5),
     *                         @OA\Property(property="is_liked", type="boolean", example=true),
     *                         @OA\Property(property="recent_like", type="null")
     *                     ),
     *                     @OA\Property(property="reply", type="object",
     *                         @OA\Property(property="count", type="integer", example=2),
     *                         @OA\Property(property="reply", type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=3),
     *                                 @OA\Property(property="comment", type="string", example="I agree!"),
     *                                 @OA\Property(property="created_at", type="string", example="2025-01-20T13:00:00Z"),
     *                                 @OA\Property(property="like", type="object",
     *                                     @OA\Property(property="count", type="integer", example=2),
     *                                     @OA\Property(property="is_liked", type="boolean", example=true),
     *                                     @OA\Property(property="recent_like", type="null")
     *                                 ),
     *                                 @OA\Property(property="user", type="object",
     *                                     @OA\Property(property="id", type="integer", example=2),
     *                                     @OA\Property(property="name", type="string", example="Jane Doe"),
     *                                     @OA\Property(property="profile_image", type="string", example="http://example.com/image.jpg")
     *                                 )
     *                             )
     *                         )
     *                     ),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="profile_image", type="string", example="http://example.com/image.jpg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error."),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error."),
     *             @OA\Property(property="code", type="integer", example=500),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function getPostAllComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|numeric|exists:posts,id',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $post = Posts::findOrFail($request->post_id);
            $comments = Comments::where("post_id", $post->id)->whereNull("comment_id")->paginate(10);
            $commentsT = $comments->map(function ($comment) use ($user) {
                $likeCount = $comment->likes->count();
                $isLiked = $comment->likes->where("user_id", $user->id)->first() ? true : false;
                $replyCount = $comment->reply->count();
                $commentUser = $comment->user;
                $reply = null;
                if ($replyCount > 0) {
                    $reply = $comment->reply->map(function ($reply) use ($user) {
                        $likeCount = $reply->likes->count();
                        $isLiked = $reply->likes->where("user_id", $user->id)->first() ? true : false;
                        $replyByUser = $reply->user;
                        return [
                            "id" => $reply->id,
                            "comment" => $reply->comment,
                            "created_at" => $reply->created_at->setTimeZone($user->time_zone ?? 'UTC'),
                            "like" => [
                                "count" => $likeCount,
                                "is_liked" =>  $isLiked,
                                "recent_like" => null
                            ],
                            "user" => [
                                "id" => $replyByUser->id,
                                "name" => $replyByUser->name,
                                "profile_image" => $replyByUser->profile_image ? asset($replyByUser->profile_image) : null
                            ]
                        ];
                    });
                }
                return [
                    "id" => $comment->id,
                    "comment" => $comment->comment,
                    "created_at" => $comment->created_at->setTimeZone($user->time_zone ?? 'UTC'),
                    "like" => [
                        "count" => $likeCount,
                        "is_liked" =>  $isLiked,
                        "recent_like" => null
                    ],
                    "reply" => [
                        "count" => $replyCount,
                        "reply" => $reply
                    ],
                    "user" => [
                        "id" => $commentUser->id,
                        "name" => $commentUser->name,
                        "profile_image" => $commentUser->profile_image ? asset($commentUser->profile_image) : null
                    ]
                ];
            });
            return ApiResponse::successResponse(
                $comments->count() > 0 ? $comments->setCollection($commentsT) : null,
                "Commnet List Retrived Successfully.",
                ProjectConstants::SUCCESS
            );
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/post/comment/like",
     *     summary="Like a Comment",
     *     description="Allows a user to like or unlike a comment.",
     *     operationId="likeComment",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="comment_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="integer", description="The ID of the comment to like or unlike.")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment Liked Successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Comment Liked Successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server Error.")
     *         )
     *     )
     * )
     */
    public function likeComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'comment_id' => 'nullable|numeric|exists:comments,id'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $comment = Comments::findOrFail($request->comment_id);
            $like = Likes::where("model_type", Comments::class)->where("model_id", $comment->id)->where("user_id", $user->id)->first();
            if ($like) {
                $like->delete();
                return ApiResponse::successResponse(null, "Comment Removed From Like Successfully.", ProjectConstants::SUCCESS);
            }
            $like = new Likes();
            $like->model_id = $comment->id;
            $like->model_type = Comments::class;
            $like->user_id = $user->id;
            $like->save();
            return ApiResponse::successResponse(null, "Comment Liked Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/reasons",
     *     summary="Get Reasons List",
     *     description="Retrieves a list of reasons with their id, name, and description.",
     *     operationId="getReasons",
     *     tags={"Repost, Delete etc. Reasons"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Issues List Get Successfully.",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Issue name"),
     *                 @OA\Property(property="description", type="string", example="Issue description")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server Error.")
     *         )
     *     )
     * )
     */
    public function getReasons(Request $request)
    {
        $response = Reasons::select("id", "name", "description")->get();
        return ApiResponse::successResponse($response, "Issues List Get Successfully", ProjectConstants::SUCCESS);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/post/report",
     *     summary="Report a Post",
     *     description="Allows users to report a post by providing a post ID and a reason ID.",
     *     operationId="reportPost",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"post_id", "reason_id"},
     *             @OA\Property(property="post_id", type="integer", example=1, description="ID of the post to report."),
     *             @OA\Property(property="reason_id", type="integer", example=2, description="ID of the reason for reporting the post.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post Reported Successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Post Reported Successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request: Post already reported.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Post already reported.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error: Invalid post_id or reason_id.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="array", items=@OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server Error.")
     *         )
     *     )
     * )
     */
    public function reportPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|numeric|exists:posts,id',
            'reason_id' => 'required|numeric|exists:reasons_types,id'
        ]);
        if ($validator->fails()) {
            return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
        }
        $user = Auth::guard("user")->user();
        $model_type =  Posts::class;
        $post = Posts::findOrFail($request->post_id);

        $postReport = Reports::where('type', 1)->where('user_id', $user->id)->where(['model_id' => $post->id, 'model_type' => $model_type])->first();
        if (!empty($postReport)) {
            return ApiResponse::errorResponse(null, "Post already reported.", ProjectConstants::BAD_REQUEST);
        }
        $postReport = new Reports();
        $postReport->model_id = $post->id;
        $postReport->model_type = $model_type;
        $postReport->user_id = $user->id;
        $postReport->reason_id = $request->reason_id;
        $postReport->save();
        return ApiResponse::successResponse(null, "Post Reported Successfully.", ProjectConstants::SUCCESS);
    }

    public function deletePost(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|numeric|exists:posts,id'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $post = Posts::findOrFail($request->post_id);
            $post->delete();
            return ApiResponse::successResponse(null, "Post Deleted Successfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }
}
