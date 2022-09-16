<?php

namespace App\Http\Controllers;

use App\Models\ratings;
use App\Models\doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingsController extends Controller
{
   public function addratings(request $request){

        $status = 'doctor not found';
        $doctortrue = doctor::where('id',$request->doctor_id);

        if($doctortrue->count()>0){
            $query = DB::table('ratings')->insert([
                'doctors_ids' => $request->doctor_id,
                'users_id' => $request->user_id,
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
