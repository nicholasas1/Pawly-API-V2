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

        if(isset($error) != 1){
            $query = doctor::insert([
                'name' => $request->name,
                'description' => $request->description,
                'profile_picture' => $request->profile,
                'graduated_since' => $request->graduated,
            ]);
            
            if($query==1){
                $status = "Registration Success";
            } 
        }
       
        return response()->JSON([
            'status' => $status
        ]);
    }

    public function getlistdoctor(request $request){

        $query = doctor::where("id",$request->id);

        if($query->count()==1){
            return response()->JSON([
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

        if($query==1){
            $status = "Update Success";
                return response()->JSON([
                    'status' => $status,
                    'result' => doctor::where('id',$request->id)->get()
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
        
        $doctorname = doctor::where('id',$request->doctor_id)->get('name');

        $query = doctor_speciality::insert([
            'doctor_id' => $request->doctor_id,
            'speciality' => $request->speciality
        ]);

        if($query==1){
            $status = "Speciality Added for dr. $doctorname";
                return response()->JSON([
                    'status' => $status
                ]);
        } else{
            $status = "Failed to add";
            return $status;
        }   
    }

    public function updatedoctorspeciality(request $request){

        $doctorname = doctor::where('id',$request->doctor_id)->get('name');

        $query = doctor_speciality::where('doctor_id',$request->doctor_id)->where('speciality',$request->specfrom)->update([
            'speciality' => $request->specto
        ]);

        if($query==1){
            $status = "Speciality Updated for dr. $doctorname";
                return response()->JSON([
                    'status' => $status
                ]);
        } else{
            $status = "Failed to Update";
            return $status;
        } 

    }

    public function deletedoctorspeciality(request $request){

        doctor_speciality::where('doctor_id',$request->doctor_id)->delete();

    }

}
