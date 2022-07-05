<?php

namespace App\Http\Controllers;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Jwt;
use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\Request;

class JWTValidator extends Controller
{
    public function createToken($user_id, $username){
        $payload = [
            'user_id' => $user_id,
            'username' => $username,
            'iat' => time(),
            'exp' => time() + 60*60*24*7
        ];
        
        $secret = 'Hello&MikeFooBar123';
        
        $token = Token::customPayload($payload, $secret);
        return $token;
    }

    public function validateToken($token){
       
        $secret = 'Hello&MikeFooBar123';

        if($token != null){
            $valid = Token::validate($token, $secret);
        }else{
            $valid = false;
            $token = '123erf.12w3se.25rds';
        }
        
        $data = Token::getPayload($token);

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
    public function refreshToken($token)
    {
        $secret = 'Hello&MikeFooBar123';
        $data = Token::getPayload($token);
        $username = User::where('id',$id['user_id'])->value('username');
        $payload = [
            'user_id' => $id['user_id'],
            'username' => $username,
            'iat' => time(),
            'exp' => time() + 60*60*24*7
        ];
        $token = Token::customPayload($payload, $secret);
        return $token;
    }
}
