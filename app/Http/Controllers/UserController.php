<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;


class UserController extends Controller
{
    //
    public function getlist(request $request)
    {
        $token = $request->header("Authorization");

        $secret = 'Hello&MikeFooBar123';
        
        $result = Token::validate($token, $secret);

        $data = Token::getPayload($token);

        $jwt = new Jwt($token);


        
        return response()->json([
            'success'=>'succes', 
            'token'     => $result,
            'data'  => $data,
            'new token' => $jwt->getToken(),
            'expired'   => Token::validateExpiration($token),
            'results'=>User::all()
        ]);
    }

    public function login(request $request)
    {
        if(is_numeric($request->username)){
            $field = 'phone_number';
        } elseif (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'username';
        }
        $query = User::where($field,$request->username)->where("password",md5($request->password));
        if($query->count()== 0){
                $status = "Invalid Username or Password";
        }else{
            $status="success";
        }

        if($query->value('status') == "Waiting Activation"){
            $status="Your account is not active. Please check your email to activate your account";
        }
        $payload = [
            'user_id' => $query->value('id'),
            'username' => $query->value('username'),
            'iat' => time(),
            'exp' => time() + 60,
            'iss' => 'localhost'
        ];
        
        $secret = 'Hello&MikeFooBar123';
        
        $token = Token::customPayload($payload, $secret);

      
        return response()->json([
            'status'=>$status, 
            'results'=> array(
                'user_id'   => $query->value('id'),
                'username'  => $query->value('username'),
                'role'      => Role::where('id',$query->value('id'))->get(),
                'token'     => $token,
            )
        ]);

    }

    public function register(request $request){
        if(User::where('username',$request->username)->count() <= 0){
            $status = "success";
            if(User::where('email',$request->email)->count() <= 0){
                $status = "success";
            }else{
                $error = 1;
                $status = "Email sudah terdaftar";
            }
        } else{
            $status = "Username sudah digunakan";
            $error = 1;
        }

        
        if(isset($error) != 1){
            $query = User::insert(
                [
                    'username' => $request->username, 
                    'password' => md5($request->password), 
                    'profile_picture' => $request->profile_picture, 
                    'nickname' => $request->nick_name, 
                    'fullname' => $request->full_name, 
                    'email' => $request->email, 
                    'birthday' => $request->tanggal_lahir, 
                    'phone_number' => $request->phone_number, 
                    'gender' => $request->gender,
                    'status' => 'Waiting Activation'
                ]
            );
            if($query == 1){
                $status = "Registration Success";
            }
        }

        return response()->json([
            'status'=>$status
        ]);
        
       
    }
}
