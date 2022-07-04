<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //
    public function getlist()
    {
        return response()->json([
            'success'=>'succes', 
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


        return response()->json([
            'status'=>$status, 
            'results'=> array(
                'user' => $query->get(['id','username']),
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

        $uppercase = preg_match('@[A-Z]@', $request->password);
        $lowercase = preg_match('@[a-z]@', $request->password);
        $number    = preg_match('@[0-9]@', $request->password);
        $specialChars = preg_match('@[^\w]@', $request->password);
        
        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($request->password) < 8) {
            $status ="Pasword setidaknya harus 8 karakter dan harus memiliki huruf besar, huruf kecil, angka, dan spesial karakter.";
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
