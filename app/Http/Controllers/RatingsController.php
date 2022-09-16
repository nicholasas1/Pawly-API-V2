<?php

namespace App\Http\Controllers;

use App\Models\ratings;
use App\Models\doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JWTValidator;
use App\Models\clinic;

class RatingsController extends Controller
{

    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }

    public function addratings(request $request){
        if($request->header("Authorization") == null){
            $token = "123456.dfdsxcfd.45gdcsxsd";
        }else{
           $token = $request->header("Authorization");
        }
        
        $result = $this->JWTValidator->validateToken($token);
        $status = 'doctor not found';
        $doctortrue = doctor::where('id',$request->doctor_id);
        //$doctortrue = clinic::where('id',$request->clinic_id);
        $user = $result['body']['user_id'];
        if($doctortrue->count()>0){
            $query = DB::table('ratings')->insert([
                'doctors_ids' => $request->doctor_id,
                //'clinic_id' => $request->clinic_id,
                'users_id' => $user,
                'ratings' => $request->rating,
                'reviews' => $request->reviews
            ]);
            if($query==1){
                return response()->JSON([
                    'status' => 'success',
                ]);
            } 
        } else{
            return response()->JSON([
                'status' => $status
            ]);
            
        }
       
    }
}
