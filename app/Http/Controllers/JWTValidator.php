<?php

namespace App\Http\Controllers;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Jwt;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\user_secret;


use Illuminate\Http\Request;

class JWTValidator extends Controller
{
    public function createToken($user_id, $username,$session_id, $secret){
        $payload = [
            'user_id' => $user_id,
            'username' => $username,
            'session_id' => $session_id,
            'iat' => time(),
            'exp' => time() + 60*60*24*7
        ];

        $token = Token::customPayload($payload, $secret);
        return $token;
    }

    public function validateToken($token){
       
        $data = Token::getPayload($token);

        $secret = user_secret::where(['user_id' => $data['user_id'],'session_id' => $data['session_id']])->value('user_secret');
        if($token != null && $secret != NULL){
            $valid = Token::validate($token, $secret);
        }else{
            $valid = false;
            $token = '123erf.12w3se.25rds';
        }
        
        $expired = Token::validateExpiration($token);

        if($valid == true and $expired == true ){
            return array(
                'status'   => 200, 
                'message'   =>'succes', 
                'body'     => $data
            );
        }else if($valid == true and $expired == false){
            return array(
                'success'   => 401,
                'message'   => 'Token Expired',

            );
        }else{
            return array(
                'status'   => 401,
                'success'   => 'Token Invalid',
            );
        }
    }
    
    public function refreshToken(request $request)
    {
        if($request->header('Authorization') == null){
            $data = Token::getPayload($request->token);
        }else{
            $data = Token::getPayload($request->header('authorization'));
        }
        $current_date_time = date('Y-m-d H:i:s');
        $secret = user_secret::where(['user_id' => $data['user_id'],'session_id' => $data['session_id']])->value('user_secret');
        $username = User::where('id',$data['user_id'])->value('username');

        $payload = [
            'user_id' => $data['user_id'],
            'username' => $username,
            'session_id' => $data['session_id'],
            'iat' => time(),
            'exp' => time() + 60*60*24*7
        ];

        if($request->token != null && $secret != NULL){
            $token = Token::customPayload($payload, $secret);
            user_secret::where(['user_id' => $data['user_id'],'session_id' => $data['session_id']])->update(
                [   
                    'firebase_token' => $request->firebase_token,
                    'updated_at' => $current_date_time
                ]);
            return array(
                'status'   => "succes", 
                'result'   => $token,
            );
        }else{
            return array(
                'status'   => 401,
                'success'   => 'Token Invalid',
            );
        }
        
        
    }

    public function logout(request $request)
    {
        $data = Token::getPayload($request->token);

        $logout = user_secret::where(['user_id' => $data['user_id'],'session_id' => $data['session_id']])->delete();
        
        if($logout == 1){
            return array(
                'status'   => "success",
                'message'   =>'Log Out Berhasil'
            );
        }else{
            return array(
                'status'   => "failed",
                'message'   =>$logout            
            );
        }
    }
}
