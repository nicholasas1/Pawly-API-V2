<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\role;
use App\Models\doctor_speciality;
use App\Models\doctor;
use Illuminate\Support\Facades\DB;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;
use App\Http\Controllers\JWTValidator;
use DeepCopy\Filter\Doctrine\DoctrineEmptyCollectionFilter;
use Carbon;

class DoctorController extends Controller
{
    //
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }

    public function regisasdoctor(request $request){

     $query = doctor::insert([
            'name' => $request->name,
            'description' => $request->description,
            'profile_picture' => $request->profile,
            'graduated_since' => $request->graduated,
            'isonline' => 'online'
        ]);
            
        if($query==1){
            $status = "Registration Success";
        } 
       
        return response()->JSON([
            'status' => $status
        ]);
    }

    public function getlistdoctor(request $request){

        $query = doctor::where("id",$request->id);

        if($query->count()==1){
            return response()->JSON([
                'id' => $query->value("id"),
                'nama' => $query->value("name"),
                'deskripsi' => $query->value("description"),
                'lulus sejak' => $query->value("graduated_since"),
                'speciality' => doctor_speciality::where('doctor_id',$query->value('id'))->get('speciality')
            ]);
        }
    }

    public function updatedoctor(request $request){

        $query = doctor::where('id',$request->id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'profile_picture' => $request->profile_picture,
            'graduated_since' => $request->graduated
        ]);

        $doctor = doctor::where('id',$request->id);

        if($query==1){
            $status = "Update Success";
                return response()->JSON([
                    'status' => $status,
                    'result' => array([
                        'id' => $doctor->value("id"),
                        'nama' => $doctor->value("name"),
                        'deskripsi' => $doctor->value("description"),
                        'lulus sejak' => $doctor->value("graduated_since"),
                        'speciality' => doctor_speciality::where('doctor_id',$doctor->value('id'))->get('speciality')
                    ])
                ]);
        } else{
            $status = "Update Failed";
            return $status;
        }
    }

    public function deletedoctorlist(request $request){

        doctor_speciality::where('doctor_id',$request->doctor_id)->delete();
        doctor::where('id',$request->doctor_id)->delete();

    }

    public function adddoctorspeciality(request $request){
        
        $doctorname = doctor::where('id',$request->doctor_id)->value('name');

        $query = doctor_speciality::insert([
            'doctor_id' => $request->doctor_id,
            'speciality' => $request->speciality
        ]);

        if($query==1){
            $status = "Speciality Added for dr. $doctorname";
                return response()->JSON([
                    'status' => $status,
                    'result' => array([
                        'id' => doctor_speciality::where('doctor_id',$request->doctor_id)->where('speciality',$request->speciality)->value('id'),
                        'speciality' => $request->speciality
                    ])
                ]);
        } else{
            $status = "Failed to add";
            return $status;
        }   
    }

    public function updatedoctorspeciality(request $request){

        $doctorid = doctor_speciality::where('id',$request->id)->value('doctor_id');

        $doctorname = doctor::where('id',$doctorid)->value('name');

        $query = doctor_speciality::where('id',$request->id)->update([
            'speciality' => $request->speciality
        ]);

        if($query==1){
            $status = "Speciality Updated for dr. $doctorname";
                return response()->JSON([
                    'status' => $status,
                    'result' => array([
                        'id' => $request->id,
                        'speciality' => $request->speciality
                    ])
                ]);
        } else{
            $status = "Failed to Update";
            return $status;
        } 

    }

    public function deletedoctorspeciality(request $request){

        doctor_speciality::where('id',$request->id)->delete();

    }

    public function lastonline(request $request){

        $isonline = $request->status;
        $doctorid = $request->id;

        
        if($isonline == 'offline'){
            $time = date('H:i:s');
            $query = doctor::where('id',$doctorid)->update(['lastonline' => $time, 'isonline' => 'offline']);

            return response()->JSON([
                'status' => 'success',
                'results' => $time
            ]);
        } else if($isonline == 'online'){

            $query = doctor::where('id',$doctorid)->update('isonline', 'online');

            return response()->JSON([
                'status' => 'success'
            ]);
        }

    }

}
