<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use App\Helpers\ProjectConstants;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserChats;
use App\Models\UserMessages;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserChatController extends Controller
{

    private function getChat($user, $to_id)
    {
        $chat = UserChats::where(function ($query) use ($user, $to_id) {
            $query->where('to_id', $to_id)->where('from_id', $user->id);
        })->orWhere(function ($query) use ($user, $to_id) {
            $query->where('to_id', $user->id)->where('from_id', $to_id);
        })->first();
        if (empty($chat)) {
            $chat = new UserChats();
            $chat->from_id = $user->id;
            $chat->to_id = $to_id;
            $chat->status = ProjectConstants::CHAT_INACTIVE;
            $chat->save();
        }
        return $chat;
    }

    public function registerSocket(Request $request){
        try {
            $user = Auth::guard('user')->user();
            $user->socket_id = $request->socket_id;
            $user->online_status = ProjectConstants::USER_ONLINE;
            $user->save();
            $respose = [
                'socket_id' => $user->socket_id,
                'user' => [
                    'id' => $user->id,
                    'online_status' => $user->online_status
                ]
            ];
            return ApiResponse::successResponse($respose, "User Connected Successfully", ProjectConstants::SUCCESS);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::successResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
    }

    public function sendMessage(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'to_id' => 'required|numeric|exists:folders,id',
                'message' => 'string',
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard('user')->user();
            $to_id = $request->input('to_id');
            $messageInput = $request->input('message');
            $chat = $this->getChat($user, $to_id);

            $messageCount = UserMessages::where('from_id', $user->id)->where("to_id", $to_id)->count();
            if ($chat->status == ProjectConstants::CHAT_INACTIVE && $messageCount > 0) {
                return ApiResponse::errorResponse(null, "You can send only one message in this invitaion", ProjectConstants::BAD_REQUEST);
            }
            
            $message = new UserMessages();
            $message->chat_id =  $chat->id;
            $message->from_id = $user->id;
            $message->to_id = $to_id;
            $message->message = $messageInput;
            $message->type = UserMessages::TEXT_MESSAGE;
            $message->is_read = ProjectConstants::MESSAGE_UNREAD;
            $message->save();

            $messageResponse = UserMessages::messageResponse($message, $user);

            return ApiResponse::successResponse([
                'chat_id' => $chat->id,
                "messages" => [
                    'date' => date('Y-m-d'),
                    "message" => $messageResponse,
                ],
                'from_socket' => $user->socket_id,
                'to_socket' => $message->toUser->socket_id,
                'unique_id' => $request->unique_id,
                'message_count' => $request->message_count
            ], "Message Sent Successfully", 200);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error.", ProjectConstants::SERVER_ERROR);
        }
        
    }
    
    public function getChatHistory(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|numeric|exists:users,id'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }
            $user = Auth::guard('user')->user();
            $toUser = User::findOrFail($request->user_id);

            $chat = $this->getChat($user, $toUser->id);
            if (empty($chat)) {
                return ApiResponse::errorResponse([], "Chat not found", 404);
            }
            $paginatedMessages = $chat->messages()->orderBy("created_at", "DESC")->paginate(10);
            $groupedMessages = $paginatedMessages->getCollection()->groupBy(function ($message) use ($user) {
                return Carbon::parse($message->created_at)->setTimeZone($user->time_zone ?? "UTC")->format('Y-m-d');
            });
            $formattedMessages = $groupedMessages->map(function ($messages, $date) use($user) {
                return [
                    'date' => $date,
                    'messages' => $messages->map(function ($message) use($user) {
                        return UserMessages::messageResponse($message, $user);
                    })
                ];
            })->values();

            $paginatedMessages->setCollection(collect($formattedMessages));

            return ApiResponse::successResponse([
                "chat_status" => $chat->status,
                "chat_id" => $chat->id,
                "to_user" => [
                    'id' => $toUser->id,
                    'name' => $toUser->name,
                    'profile_image' => $toUser->profile_image ? asset($toUser->profile_image) : null,
                    'online_status' => $toUser->online_status,
                    'last_seen' => $toUser->updated_at, 
                ],
                'messages' => $paginatedMessages->items(),
                'pagination' => [
                    'total' => $paginatedMessages->total(),
                    'per_page' => $paginatedMessages->perPage(),
                    'current_page' => $paginatedMessages->currentPage(),
                    'last_page' => $paginatedMessages->lastPage(),
                    'next_page_url' => $paginatedMessages->nextPageUrl(),
                    'prev_page_url' => $paginatedMessages->previousPageUrl()
                ]
            ], "Messages fetched successfully", 200);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "User Not Found", 404);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", 500);
        }
    }

    public function reactToChatRequest(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|numeric|in:0,1',
                'chat_id' => 'required|numeric|exists:chats,id'
            ]);
            if ($validator->fails()) {
                return ApiResponse::validationResponse($validator->errors()->all(), ProjectConstants::VALIDATION_ERROR);
            }

            $chat = UserChats::findOrFail($request->chat_id);
            if ($chat->status == ProjectConstants::CHAT_ACTIVE) {
                return ApiResponse::successResponse(null, "Already Accepted.",ProjectConstants::BAD_REQUEST);
            }

            if ($request->status == ProjectConstants::CHAT_ACTIVE) {
                $chat->status = ProjectConstants::CHAT_ACTIVE;
                $chat->save();
                return ApiResponse::successResponse(null, "Request Accepted Succesfully", ProjectConstants::SUCCESS);
            }
            $chat->delete();
            return ApiResponse::successResponse(null, "Request Rejected Succesfully", ProjectConstants::SUCCESS);
        } catch (ModelNotFoundException $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Chat Not Found.", ProjectConstants::NOT_FOUND);
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", ProjectConstants::SERVER_ERROR);
        }
    }

    public function getChatRequests(Request $request){
        try {
            $user = Auth::guard("user")->user();
            $chatRequests = UserChats::where("to_id", $user->id)->where("status", 0)->paginate(10);
            $chatRequestsTransform = $chatRequests->map(function($chat){
                return [
                    "id" => $chat->id,
                    "status" => 0,
                    "user" =>[
                        "id" => $chat->fromUser->id,
                        "name" => $chat->fromUser->name,
                        "profile_image" => $chat->fromUser->profile_image ? asset($chat->fromUser->profile_image) : null,
                        "online_status" => 1,
                        "last_seen" => $chat->fromUser->updated_at 
                    ],
                    "message" => [
                        "count" => 0,
                        "last_message" => null
                    ],
                    "created_at" => $chat->created_at 
                ];
            });
            return ApiResponse::successResponse(
                $chatRequests->count() > 0 ? $chatRequests->setCollection($chatRequestsTransform) : null,
                "Chat Request list Got successfully.",
                ProjectConstants::SUCCESS    
            );
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", 500);
        }
    }

    public function getChatLists(Request $request){
        try{    
            $user = Auth::guard("user")->user();
            $chatLists = UserChats::where("to_id", $user->id)->where("status", 1)->paginate(10);
            $chatListsTransform = $chatLists->map(function($chat){
                return [
                    "id" => $chat->id,
                    "status" => 1,
                    "user" =>[
                        "id" => $chat->fromUser->id,
                        "name" => $chat->fromUser->name,
                        "profile_image" => $chat->fromUser->profile_image ? asset($chat->fromUser->profile_image) : null,
                        "online_status" => 1,
                        "last_seen" => $chat->fromUser->updated_at
                    ],
                    "message" => [
                        "count" => 0,
                        "last_message" => null
                    ],
                    "created_at" => $chat->created_at, 
                ];
            });
            return ApiResponse::successResponse(
                $chatLists->count() > 0 ? $chatLists->setCollection($chatListsTransform) : null,
                "Chat Request list Got successfully.",
                ProjectConstants::SUCCESS    
            );
        } catch (Exception $ex) {
            Log::error($ex);
            return ApiResponse::errorResponse(null, "Server Error", 500);
        }
    }

}
