<?php

namespace App\Http\Controllers;
use App\Http\Controllers\JWTValidator;
use App\Models\otp_table;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Http\Request;

class otpController extends Controller
{
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
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
            $query = otp_table::insert([
                'user_id' => $user,
                'otp' => rand(100000, 999999),
                'phone_number' => $request->code_area.$request->phone_number,
                'valid_until' => Carbon::now()->timestamp + (2*60),
                'created_at' => date("Y-m-d h:i:sa")
            ]);

            if($query == 1){
                $status = "success";
                $msg = "Check your whatsapp and verification before 2 minutes";
            }else{
                $status = "error";
                $msg = "Check agin your phone number and please try againt";
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

    public function validateOTP(request $request){
        if($request->header("Authorization") == null){
            $token = "123456.dfdsxcfd.45gdcsxsd";
        }else{
           $token = $request->header("Authorization");
        }
        $result = $this->JWTValidator->validateToken($token);
        if($result['status'] == 200){
            $user = $result['body']['user_id'];


            $query = otp_table::where('user_id',$user)->where('phone_number',$request->code_area.$request->phone_number)->where('otp',$request->otp);

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


            $query = otp_table::where('user_id',$user)->where('phone_number',$request->code_area.$request->phone_number)->where('valid_until','<',Carbon::now()->timestamp);

            if($query->count() == 1){
                $UpdateOTP = otp_table::find($query->value('id'))->update(
                    [
                        'otp' => rand(100000, 999999),
                        'valid_until' => Carbon::now()->timestamp + (2*60),
                    ]
                );
                if($UpdateOTP == 1){
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
