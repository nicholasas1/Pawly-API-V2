<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
            'results'=>User::where('username', '=', $request->Username) ->orWhere('email', 'like', $request->Password)
            ->get('id')
        ]);
       
    }
}
