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

        return response()->json([
            'success'=>'succes', 
            'results'=> array(
                //'role' => User::where('username',$request->username)->orWhere('email',$request->username)->where('password',md5($request->password))->get(['id','username']),
                'user' => User::where($field,$request->username)->where('password',md5($request->password))->get(['id','username']),
            )
        ]);

    }
}
