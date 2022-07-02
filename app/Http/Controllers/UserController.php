<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use DB;

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
        
        return response()->json([
            'success'=>'succes', 
            'results'=> array(
                'role' => User::where('username', '=', $request->Username)->orWhere('email', '=', $request->Username) ->Where('password', '=', $request->Password)->get(['id','username']),
                'user' => User::where('username', '=', $request->Username)->orWhere('email', '=', $request->Username) ->Where('password', '=', $request->Password)->get(['id','username'])
            )
        ]);
       
    }
}
