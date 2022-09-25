<?php

namespace App\Http\Controllers;

use App\Models\servicefavourite;
use App\Http\Requests\StoreservicefavouriteRequest;
use App\Http\Requests\UpdateservicefavouriteRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\JWTValidator;
class ServicefavouriteController extends Controller
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

            $query = servicefavourite::insert([
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

            $query = servicefavourite::where('userids',$userid)->where('service_meta',$request->service_meta)->where('service_id',$request->service_id)->delete();
            
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

            $query = servicefavourite::where('userids',$userid)->get();
            foreach($query as $fav){
                $arr = ['userid' => $fav->userid, 'service_meta' => $fav->service_meta, 'service_id' => $fav->service_id];
            }
            if($query==1){
                $status = 'success';
            } 
        } 
        return response()->JSON([
            'status' => $status,
            'results' => $arr
        ]);
       
    }
}
