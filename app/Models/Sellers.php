<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class Sellers extends Model
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;
    protected $table = "users";
    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function genrateEmailOtp(){
        $otp = mt_rand(1000, 9999);
        $emailOtp = UserOtps::where(["user_id" => $this->id, "type" => ProjectConstants::EMAIL_OTP])->first();
        if(!$emailOtp){
            $emailOtp = new UserOtps();
            $emailOtp->user_id = $this->id;
            $emailOtp->type = ProjectConstants::EMAIL_OTP;
        }
        $emailOtp->otp =  $otp;
        $emailOtp->otp =  $otp;
        $emailOtp->save();
        return $emailOtp->otp;
    }

    public function genratePhoneOtp(){
        $otp = mt_rand(1000, 9999);
        $emailOtp = UserOtps::where(["user_id" => $this->id, "type" => ProjectConstants::PHONE_OTP])->first();
        if(!$emailOtp){
            $emailOtp = new UserOtps();
            $emailOtp->user_id = $this->id;
            $emailOtp->type = ProjectConstants::PHONE_OTP;
        }
        $emailOtp->otp =  $otp;
        $emailOtp->save();
        return $emailOtp->otp;
    }

    public static function passwordResetToken(){
        try{
            $remember_token = Str::random(64);
            return $remember_token;
        } catch(Exception $ex){
            return null;
        }
    }
}

