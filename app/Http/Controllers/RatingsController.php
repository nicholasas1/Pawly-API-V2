<?php

namespace App\Http\Controllers;

use App\Models\ratings;
use App\Models\doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\JWTValidator;
use App\Models\clinic;
use Carbon\Carbon;
use App\Models\User;
use App\Models\orderservice;


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
        $status = 'error';
        $query = orderservice::where('order_id','=',$request->order_id);
        if($query->count() != 1){
            $msg = 'Booking ID not found';
        }else{
            if($query->value('type')== "doctor"){
                $validate = doctor::where('id',$query->value('service_id'));
            }else if($query->value('type')== "clinic"){
                $validate = clinic::where('id',$query->value('service_id'));
            }

            $user = $result['body']['user_id'];
            if($validate->count()>0){
                $query = DB::table('ratings')->insert([
                    'service_id' => $query->value('service_id'),
                    'service_meta' => $query->value('type'),
                    'service_meta' => $request->order_id,
                    'booking_id' => $user,
                    'ratings' => $request->rating,
                    'reviews' => $request->reviews,
                    'timereviewed' => Carbon::now()->timestamp
                ]);
                if($query==1){
                    $msg = '';
                    $status = 'success';

                }else{
                    $msg = 'Failed input rating';
                    $status = 'error';
                }
            } else{
                $msg = 'Service ID not found';
            }
        }

        return response()->JSON([
            'status' => $status,
            'msg'   => $msg
        ]);
       
       
        //$validate = clinic::where('id',$request->clinic_id);
       
    }

    public function ratingList(request $request){
        if($request->limit==NULL){
            $limit = 10;
        } else{
            $limit = $request->limit;
        }
    
        if($request->page==NULL){
            $page = 0;
        } else{
            $page = ($request->page - 1) * $limit;
        }

        $query = ratings::where('doctors_ids','=',$request->doctor_id);
        $query2 = ratings::where('doctors_ids','=',$request->doctor_id);
        $result=[];  
            
        foreach($query->limit($limit)->offset($page)->get() as $arr){
            $method = array(
                'id' => $arr['id'],
                'doctor_id'=>$arr['doctors_ids'],
                'user_id'=>$arr['users_id'],
                'user_name'=>User::where('id','=',$arr['users_id'])->value('username'),
                'user_nickname'=>User::where('id','=',$arr['users_id'])->value('nickname'),
                'ratings'=>$arr['ratings'],
                'reviews'=>$arr['reviews'],
                'timereviewed'=>$arr['timereviewed'],
            );
            array_push($result, $method);
        }

        return response()->JSON([
            'status' => 'success',
            'total_data'=>$query2->count(),  
            'total_page'=> ceil($query2->count() / $limit),
            'avarge_rating' => $query2->avg('ratings'),
            'result' => $result
        ]);
    }
}
