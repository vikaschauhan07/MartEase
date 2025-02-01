<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\AwsHelper;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\UserDocumnents;
use App\Models\UserFolders;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserFolderDocumnetController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/user/create-new-folder",
     *     summary="Create a New Folder",
     *     description="Create a new folder for the user. Optionally, a folder can be nested under a parent folder by specifying the parent folder ID.",
     *     operationId="createNewFolder",
     *     tags={"User Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *      required=true,
     *      @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", description="The Name of Folder", example="Driving Licence"),
     *             @OA\Property(property="folder_id", type="integer", description="ID of the parent Folder if any (optional)", example=2)
     *      )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Folder created successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Folder Created successfully."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request: Validation errors.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string", example="Folder name is required."))
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
    public function createNewFolder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:2|max:35',
                'folder_id' => 'nullable|numeric|exists:folders,id'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $folder = new UserFolders();
            $folder->name = $request->name;
            $folder->user_id = $user->id;
            if (isset($request->folder_id)) {
                $folder->folder_id = $request->folder_id;
            }
            $folder->save();
            return ApiResponse::successResponse(null, "Folder Created succesfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SUCCESS);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/edit-folder",
     *     summary="Edit Folder",
     *     description="Edit the details of an existing folder. You can change the name of the folder.",
     *     operationId="editFolder",
     *     tags={"User Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Updated Folder", description="The new name for the folder."),
     *              @OA\Property(property="folder_id", type="integer", example=1, description="The ID of the folder to edit.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Folder updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Folder Updated successfully."),
     *             @OA\Property(property="data", type="object", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request: Validation errors or permission issue.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You don't have permission to edit this folder."),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string", example="Folder name is required."))
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
    public function editFolder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:2|max:35',
                'folder_id' => 'required|numeric|exists:folders,id'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $folder = UserFolders::where("id", $request->folder_id)->first();
            if ($folder->user_id != $user->id) {
                return ApiResponse::successResponse(null, "You don't have permission to edit this folder.", ProjectConstants::BAD_REQUEST);
            }
            $folder->name = $request->name;
            $folder->save();
            return ApiResponse::successResponse(null, "Foder Updated succesfully.", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SUCCESS);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/get-all-folders",
     *     summary="Get All Folders",
     *     description="Retrieve a list of all folders for the authenticated user, including the total files count in each folder.",
     *     operationId="getAllFolders",
     *     tags={"User Documents"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Folders retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Folders retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Folder Name"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-20T10:00:00"),
     *                 @OA\Property(property="total_files", type="integer", example=5),
     *                 @OA\Property(property="total_folders", type="integer", example=0)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request: No folders found or other issue with data.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="No folders found.")
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
    public function getAllFolders(Request $request)
    {
        try {
            $user = Auth::guard("user")->user();
            $folders = UserFolders::where("user_id", $user->id)
                ->whereNull("parent_folder_id")
                ->select("id", "name", "created_at")
                ->paginate(10);

            $folders->setCollection(
                $folders->map(function ($folder) {
                    return [
                        "id" => $folder->id,
                        "name" => $folder->name,
                        "created_at" => $folder->created_at,
                        "total_files" => $folder->files->count(),
                        "total_folders" => 0,
                    ];
                })
            );
            if ($folders->count() < 1) {
                $folders = null;
            }
            return ApiResponse::successResponse(
                $folders,
                "Folders retrieved successfully.",
                ProjectConstants::SUCCESS
            );
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/add-files-to-folder",
     *     summary="Add documents to folder",
     *     description="Uploads documents to a specific folder for the user",
     *     operationId="addDocumentsToFolder",
     *     tags={"User Documents"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Add documents to the folder",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"folder_id", "documents"},
     *                 @OA\Property(
     *                     property="folder_id",
     *                     type="integer",
     *                     description="ID of the folder"
     *                 ),
     *                 @OA\Property(
     *                     property="documents",
     *                     type="array",
     *                     items=@OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Documents to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File added to the folder successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="File added to the folder.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation Error")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Folder not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function addDocumentsToFolder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'folder_id' => 'required|numeric|exists:folders,id',
                'documents' => 'required|array',
                'documents.*' => 'file|max:10240',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $folder = UserFolders::findOrFail($request->folder_id);
            DB::beginTransaction();
            $filePath = "Docments/" . $user->name . "/";
            if ($request->has('documents') && !empty($request->file('documents'))) {
                foreach ($request->file('documents') as $uploadedFile) {
                    $userDocumnents = new UserDocumnents();
                    $userDocumnents->user_id = $user->id;
                    $userDocumnents->folder_id = $request->folder_id;
                    $userDocumnents->name = Str::limit($uploadedFile->getClientOriginalName(), 155, '');
                    $userDocumnents->file = AwsHelper::uploadFile($uploadedFile, $filePath);
                    $userDocumnents->save();
                }
            }
            DB::commit();
            return ApiResponse::successResponse(null, "File added to the folder.",  ProjectConstants::SUCCESS);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Folder not found", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/delete-folder",
     *     summary="Delete a folder",
     *     description="This endpoint allows users to delete a folder based on the folder ID.",
     *     tags={"User Documents"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="folder_id",
     *         in="query",
     *         description="ID of the folder to be removed.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Folder removed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Folder removed successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid folder ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bad request."),
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
    public function deleteFolder(Request $request)
    {
        try {
            UserFolders::where("id", $request->folder_id)->delete();
            return ApiResponse::successResponse(
                null,
                "Folder removed successfully.",
                ProjectConstants::SUCCESS
            );
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/get-folder-by-id",
     *     summary="Get folder by ID",
     *     description="This endpoint allows users to retrieve a folder by its ID along with its files.",
     *     tags={"User Documents"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="folder_id",
     *         in="query",
     *         description="ID of the folder to retrieve.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Folder retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Folder Name"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-20T10:00:00Z"),
     *             @OA\Property(property="total_files", type="integer", example=5),
     *             @OA\Property(property="total_folders", type="integer", example=0),
     *             @OA\Property(property="folders", type="null"),
     *             @OA\Property(
     *                 property="files",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Document Name"),
     *                     @OA\Property(property="file", type="string", example="https://example.com/path/to/file"),
     *                     @OA\Property(property="thumbnail", type="null"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-20T10:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid folder ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bad request."),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Folder not found"),
     *             @OA\Property(property="code", type="integer", example=404),
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
    public function getFolderById(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'folder_id' => 'required|numeric|exists:folders,id'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard("user")->user();
            $folder = UserFolders::where("user_id", $user->id)->findOrFail($request->folder_id);
            $files = UserDocumnents::where("user_id", $user->id)->where("folder_id", $folder->id)->paginate(10);
            $files->setCollection(
                $files->map(function ($file) use ($user) {
                    return [
                        "id" => $file->id,
                        "name" => $file->name,
                        "file" => asset($file->file),
                        "thumbnail" => null,
                        "created_at" =>  Carbon::parse($file->created_at)->timezone($user->time_zone)
                    ];
                })->filter()->values()
            );
            $response = [
                "id" => $folder->id,
                "name" => $folder->name,
                "created_at" => $folder->created_at,
                "total_files" => $folder->files->count(),
                "total_folders" => 0,
                "folders" => null,
                "files" => $files->count() > 0 ? $files : null
            ];

            return ApiResponse::successResponse(
                $response,
                "File added to the folder.",
                ProjectConstants::SUCCESS
            );
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Folder not found", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/delete-file",
     *     summary="Delete a file",
     *     description="This endpoint allows users to delete a file by its ID.",
     *     tags={"User Documents"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="file_id",
     *         in="query",
     *         description="ID of the file to delete.",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File removed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File removed successfully."),
     *             @OA\Property(property="code", type="integer", example=200),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, invalid file ID",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bad request."),
     *             @OA\Property(property="code", type="integer", example=400),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File not found"),
     *             @OA\Property(property="code", type="integer", example=404),
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
    public function deleteFile(Request $request)
    {
        try {
            UserDocumnents::where("id", $request->file_id)->delete();
            return ApiResponse::successResponse(
                null,
                "File removed successfully.",
                ProjectConstants::SUCCESS
            );
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }
}
