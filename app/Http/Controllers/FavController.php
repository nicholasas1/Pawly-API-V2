<?php

namespace App\Http\Controllers;

use App\Models\fav;
use Illuminate\Http\Request;

class FavController extends Controller
{
    protected $JWTValidator;
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }

    public function addfav(request $request){
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $status = 'error';
        if($result['status'] == 200){
            $userid = $result['body']['user_id'];

            $query = fav::insert([
                'usersids' => $userid,
                'service_meta' => $request->service_meta,
                'service_id' => $request->service_id
            ]);
            
            if($query==1){
                $status = 'success';
            } 
        }
        
        return response()->JSON([
            'status' => $status
        ]);
    }

    public function deletefav(request $request){
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $status = 'error';
        if($result['status'] == 200){
            $userid = $result['body']['user_id'];

            $query = fav::where('usersids',$userid)->where('service_meta',$request->service_meta)->where('service_id',$request->service_id)->delete();
            
            if($query==1){
                $status = 'success';
            } 
        }
        
        return response()->JSON([
            'status' => $status
        ]);
    }

    public function getuserfavlist(request $request){
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $status = 'error';
        $arr = [];
        if($result['status'] == 200){
            $userid = $result['body']['user_id'];

            $query = fav::where('usersids',$userid)->get();
            foreach($query as $fav){
                $arr = ['service_meta' => $fav->service_meta, 'service_id' => $fav->service_id];
            }
            $status = 'success';
        } 
        return response()->JSON([
            'status' => $status,
            'results' => $arr
        ]);
       
    }
}
