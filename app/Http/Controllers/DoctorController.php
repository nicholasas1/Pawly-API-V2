<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\role;
use App\Models\doctor;
use Illuminate\Support\Facades\DB;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;
use App\Http\Controllers\JWTValidator;

class DoctorController extends Controller
{
    //
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }

    public function regisasdoctor(request $request){

        if(doctor::where('name',$request->name)->count() <= 0){
            $status = "success";
        } else{
            $status = "nama dokter sudah digunakan";
            $error = 1;
        }

        if($error != 1){
            $query = doctor::insert([
                'name' => $request->name,
                'description' => $request->description,
                'profile_picture' => $request->profile,
                'experience' => $request->experience
            ]);
    
            if($query==1){
                $status = "Registration Success";
            } 
        }
       
        return response()->JSON([
            'status' => $status
        ]);
    }

}
