<?php

namespace App\Http\Controllers;
use App\Http\Controllers\JWTValidator;
use App\Http\Controllers\whatsapp_notif;
use App\Models\otp_table;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

use Illuminate\Http\Request;

class otpController extends Controller
{
    public function __construct(JWTValidator $JWTValidator, whatsapp_notif $whatsapp)
    {
        $this->JWTValidator = $JWTValidator;
        $this->whatsapp = $whatsapp;
    }


    public function makeOTP(request $request){
        if($request->header("Authorization") == null){
            $token = "123456.dfdsxcfd.45gdcsxsd";
        }else{
           $token = $request->header("Authorization");
        }
        $result = $this->JWTValidator->validateToken($token);
        if($result['status'] == 200){
            $user = $result['body']['user_id'];
            $timestamp = Carbon::now()->timestamp;
            $otp = rand(100000, 999999);
            $valid_until = $timestamp + (10*60);
            $query = otp_table::insertGetId([
                'user_id' => $user,
                'otp' => $otp,
                'phone_number' => $request->code_area.$request->phone_number,
                'valid_until' =>  $valid_until,
                'created_at' => date("Y-m-d h:i:sa")
            ]);
            $chat = "Kode OTP kamu adalah ".$otp.". Jaga kerahasiaan kode OTP kamu, Jangan berikan kode OTP kepada siapapun.";
            
            $wa = $this->whatsapp->sendWaText($request->code_area.$request->phone_number, $chat);
            $decode = json_decode($wa, true);
            if($decode['status'] == true){
                $status = "success";
                $msg = "Check your whatsapp and verification before 5 minutes";
                $id = $query;
                
            }else{
                $status = "error";
                $msg = "Check your phone number and please try again";
                $id = null;
            }
        }else{
            $status = "error";
            $msg = $result;
        }
        
        return response()->JSON([
            'status' => $status,
            'message' => $msg,
            'otp_id' => $id,
            'valid_until' => $valid_until
        ]);
    }

    public function validateOTP(request $request){
        if($request->header("Authorization") == null){
            $token = "123456.dfdsxcfd.45gdcsxsd";
        }else{
           $token = $request->header("Authorization");
        }
        $result = $this->JWTValidator->validateToken($token);
        if($result['status'] == 200){
            $user = $result['body']['user_id'];


            $query = otp_table::where('user_id',$user)->where('phone_number',$request->code_area.$request->phone_number)->where('otp',$request->otp)->where('id',$request->otp_id);

            if($query->count() == 1){
                if($query->value('valid_until') >= Carbon::now()->timestamp){
                    otp_table::where('id',$query->value('id'))->delete();
                    $AddPhone = User::find($user)->update(
                        [
                            'phone_number' => $request->code_area.$request->phone_number, 
                            'update_at' =>  date('Y-m-d H:i:s')
                        ]
                    );
                    if($AddPhone == 1){
                        $status = "succes";
                        $msg = "Verification succes";
                    }else{
                        $status = "error";
                        $msg = "Failed change phone number";
                    }
                }else{
                    $status ='error';
                    $msg = "Your verification code has expired.";
                }
               
            }else{
                $status = "error";
                $msg = "Check agin your OTP";
            }
        }else{
            $status = "error";
            $msg = $result;
        }
        



        return response()->JSON([
            'status' => $status,
            'message' => $msg
        ]);
    }

    public function resend(request $request){
        if($request->header("Authorization") == null){
            $token = "123456.dfdsxcfd.45gdcsxsd";
        }else{
           $token = $request->header("Authorization");
        }
        $result = $this->JWTValidator->validateToken($token);
        if($result['status'] == 200){
            $user = $result['body']['user_id'];


            $query = otp_table::where('id',$request->otp_id)->where('valid_until','<',Carbon::now()->timestamp);

            if($query->count() == 1){
                $otp = rand(100000, 999999);
                $UpdateOTP = otp_table::find($request->otp_id)->update(
                    [
                        'otp' => $otp,
                        'valid_until' => Carbon::now()->timestamp + (10*60),
                    ]
                );
                $chat = "Kode OTP kamu adalah ".$otp.". Jaga kerahasiaan kode OTP kamu, Jangan berikan kode OTP kepada siapapun.";
            
                $wa = $this->whatsapp->sendWaText($request->code_area.$request->phone_number, $chat);
                $decode = json_decode($wa, true);

                if($UpdateOTP == 1 && $decode['status'] == true){
                    $status = "succes";
                    $msg = "We send new OTP, Check your whatsapp";
                }else{
                    $status = "error";
                    $msg = "Can't create new OTP";
                }
                
               
            }else{
                $status = "error";
                $msg = "OTP still valid, please check your whatsapp";
            }
        }else{
            $status = "error";
            $msg = $result;
        }
        



        return response()->JSON([
            'status' => $status,
            'message' => $msg
        ]);
    }
}
