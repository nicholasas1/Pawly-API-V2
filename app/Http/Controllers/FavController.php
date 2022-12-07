<?php

namespace App\Http\Controllers;

use App\Models\fav;
use Illuminate\Http\Request;
use App\Models\doctor;
use App\Models\clinic;
use App\Models\doctor_speciality;
use Carbon\Carbon;

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
        $partner_detail = [];
        if($result['status'] == 200){
            $userid = $result['body']['user_id'];

            $query = fav::where('usersids',$userid)->get();
            
            foreach($query as $fav){
                if($fav->service_meta == 'doctor'){
                    $detail = doctor::where('id','like',  $fav->service_id);
                    $partner_detail = [
                        'name' => $detail->value('doctor_name'),
                        'profile_picture' => $detail->value('profile_picture'),
                        'experience' =>  Carbon::now()->year-$detail->value('worked_since'),
                        'speciality' => doctor_speciality::where('doctor_id',$query->value('doctors.id'))->get(),
                        'opening_hour'=> null
                    ];
                }else if($fav->service_meta == 'clinic'){
                    $detail = clinic::where('id','like',  $fav->service_id);
                    $partner_detail = [
                        'name' => $detail->value('clinic_name'),
                        'profile_picture' => $detail->value('profile_picture'),
                        'experience' =>  null,
                        'speciality' => null,
                        'opening_hour'=> null
                    ];
                };
                $data = [
                    'service_meta' => $fav->service_meta, 
                    'service_id' => $fav->service_id,
                    'partner_detail' => $partner_detail
                ];
                array_push($arr, $data);
            }
            $status = 'success';
        } 
        return response()->JSON([
            'status' => $status,
            'results' => $arr
        ]);
       
    }
}
