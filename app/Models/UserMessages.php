<?php

namespace App\Models;

use App\Helpers\ProjectConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMessages extends Model
{
    use SoftDeletes;
    protected $table = "messages";


    const TEXT_MESSAGE = 1;
    const FILE_MESSAGE = 2;
    const POST_MESSAGE = 3;
    const REPLY_MESSAGE = 4;
    const FORWORD_MESSAGE = 5;

    public function fromUser(){
        return $this->belongsTo(User::class, "from_id");
    }

    public function toUser(){
        return $this->belongsTo(User::class, "to_id");
    }

    public static function messageResponse($message, $user){
        if (!$message) {
            return null;
        }
        if ($message->to_id == $user->id && $message->is_read == 0) {
            $message->is_read = 1;
            $message->save();
        }
        
        $direction = $user->id === $message->from_id ? 'SENT' : 'RECEIVED';
        $additionalInfo = null;
        if ($message->type == UserMessages::FILE_MESSAGE && !empty($message->files)) {
            $additionalInfo = $message->files->map(function ($file) {
                if (!empty($file)) {
                    return [
                        'id' => $file->id,
                        'file' => !empty($file->file) ? asset($file->file) : null,
                        'thumbnail' => !empty($file->thumbnail) ? asset($file->thumbnail) : null,
                        'media_type' => $file->file_type,
                    ];
                }
                return null;
            })->filter();
        } elseif ($message->type == UserMessages::POST_MESSAGE && !empty($message->post)) {
            $additionalInfo = Posts::postResponse($message->post, $user);
        } elseif ($message->type === UserMessages::REPLY_MESSAGE) {
            if (!empty($message->message_id)) {
                $replyMessage = UserMessages::where("id", $message->message_id)->first();
                $additionalInfo = UserMessages::messageResponse($replyMessage, $user);
            }
        } 

        $contentKey = match ($message->type) {
            2 => 'message_content',
            3 => 'post_content',
            4 => 'message_reply',
            default => 'message_content',
        };


        $returnResponse = [
            'id' => $message->id,
            'from' => [
                'id' => $message->fromUser->id,
                'name' => $message->fromUser->name,
                'profile_image' => $message->fromUser->profile_image ? asset($message->fromUser->profile_image) : null,
            ],
            'to' => [
                'id' => $message->toUser->id,
                'name' => $message->toUser->name,
                'profile_image' => $message->toUser->profile_image ? asset($message->toUser->profile_image) : null,
            ],
            'message' => $message->message,
            'message_type' => ProjectConstants::MESSAGE_TYPE_ARRAY[$message->type],
            $contentKey => $additionalInfo,
            'is_read' => $message->is_read,
            'message_direction' => $direction,
            'created_at' => $message->created_at->setTimeZone($user->time_zone ?? 'UTC'),
        ];
        if ($message->is_forword == 1) {
            $returnResponse["is_forword"] = 1;
        }
        return  $returnResponse;
    }

}
