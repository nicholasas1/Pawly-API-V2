<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\role;
use App\Models\doctor_speciality;
use App\Models\doctor;
use App\Models\clinic_doctor;
use Illuminate\Support\Facades\DB;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;
use App\Http\Controllers\JWTValidator;
use DeepCopy\Filter\Doctrine\DoctrineEmptyCollectionFilter;
use Carbon\Carbon;

class DoctorController extends Controller
{
    //
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }

    public function regisasdoctor(request $request){

     $query = doctor::insert([
            'users_ids' => $request->id,
            'doctor_name' => $request->name,
            'description' => $request->description,
            'profile_picture' => $request->profile,
            'graduated_since' => $request->graduated,
            'isonline' => 'online'
        ]);

        $doctorid = doctor::where('users_ids',$request->id)->value('id');
            
        if($query==1){
            $queries = role::insert([
                'userId' => $request->id,
                'meta_role' => 'Doctor',
                'meta_id' => $doctorid
            ]);
            $status = "Registration Success";
        } else{
            $status = 'error';
        }
       
        return response()->JSON([
            'status' => $status
        ]);
    }

    public function getlistdoctor(request $request){

        $query = doctor::where("id",$request->id);

        if($query->count()==1||$query->value('isonline')=='online'){
            return response()->JSON([
                'status' => 'success',
                'id' => $query->value("id"),
                'doctor_name' => $query->value("doctor_name"),
                'description' => $query->value("description"),
                'graduated_since' => $query->value("graduated_since"),
                'graduated_from' => $query->value("graduated_from"),
                'chat_price' => $query->value("chat_price"),
                'isonline' => $query->value("isonline"),
                'speciality' => doctor_speciality::where('doctor_id',$query->value('id'))->get('speciality')
            ]);
        } else if($query->count()==1||$query->value('isonline')=='offline'){
            return response()->JSON([
                'status' => 'success',
                'id' => $query->value("id"),
                'doctor_name' => $query->value("doctor_name"),
                'description' => $query->value("description"),
                'graduated_since' => $query->value("graduated_since"),
                'graduated_from' => $query->value("graduated_from"),
                'chat_price' => $query->value("chat_price"),
                'isonline' => $query->value("isonline"),
                'lastonline' => $query->value("lastonline"),
                'speciality' => doctor_speciality::where('doctor_id',$query->value('id'))->get('speciality')
            ]);
        } else{
            return response()->JSON([
                'status' => 'doctor not found'
            ]);
        }
    }

    public function updatedoctor(request $request){

        $query = doctor::where('id',$request->id)->update([
            'doctor_name' => $request->name,
            'description' => $request->description,
            'profile_picture' => $request->profile_picture,
            'graduated_since' => $request->graduatedsince,
            'graduated_from' => $request->gradutedfrom
        ]);

        $doctor = doctor::where('id',$request->id);

        if($query==1){
            $status = "Update Success";
                return response()->JSON([
                    'status' => $status,
                ]);
        } else{
            $status = "update failed";
            return $status;
        }
    }

    public function deletedoctorlist(request $request){

        $delete_speciality = doctor_speciality::where('doctor_id',$request->doctor_id)->delete();
        $delete_doctor = doctor::where('id',$request->doctor_id)->delete();

        if($delete_speciality==1&&$delete_doctor==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else {
            return response()->JSON([
                'status' => 'doctor not found'
            ]);
        }

    }

    public function adddoctorspeciality(request $request){

        $query = doctor_speciality::insert([
            'doctor_id' => $request->doctor_id,
            'speciality' => $request->speciality
        ]);

        if($query==1){
            $status = "success";
                return response()->JSON([
                    'status' => $status
                ]);
        } else{
            $status = "doctor not found";
            return response()->JSON([
                'status'=> $status
            ]);
        }   
    }

    public function updatedoctorspeciality(request $request){

        $doctorid = doctor_speciality::where('id',$request->id)->value('doctor_id');

        $doctorname = doctor::where('id',$doctorid)->value('doctor_name');

        $query = doctor_speciality::where('id',$request->id)->update([
            'speciality' => $request->speciality
        ]);

        if($query==1){
            $status = "success";
                return response()->JSON([
                    'status' => $status,
                    'result' => array([
                        'id' => $request->id,
                        'speciality' => $request->speciality
                    ])
                ]);
        } else{
            $status = "update failed";
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
            $time = Carbon::now()->timestamp;
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

    public function filtersearch(request $request){

        $doctorspeciality = $request->speciality;
        if($request->order=='z-a'){
            $order = 'desc';
        } else{
            $order = 'asc';
        }
        if($request->price=='expe'){
            $price = 'desc';
        } else{
            $price = 'asc';
        }
        if($doctorspeciality==NULL){
            $query = DB::table('clinic_doctors')
            ->join('clinics','clinic_doctors.clinic_id','=','clinics.id')
            ->join('doctors','clinic_doctors.doctor_id','=','doctors.id')
            ->join('doctor_specialities', 'clinic_doctors.doctor_id','=','doctor_specialities.doctor_id')
            ->select(['clinic_doctors.doctor_id','clinic_doctors.clinic_id','doctors.doctor_name','clinics.clinic_name','clinics.lat','clinics.long','doctors.description','doctor_specialities.speciality','doctors.profile_picture','doctors.graduated_since','doctors.vidcall_price','doctors.chat_price','doctors.offline_price','doctors.isonline'])
            ->orderBy('doctors.isonline','desc')->orderBy('doctors.doctor_name',$order)
            // ->orderBy('doctors.vidcall_price',$vidcall)
            ->orderBy('doctors.chat_price',$price)
            // ->orderBy('doctors.offline_price',$onsite)
            ->get();
            return response()->JSON([
                'results' => $query
            ]);
        } else{
            $query = DB::table('clinic_doctors')
            ->join('clinics','clinic_doctors.clinic_id','=','clinics.id')
            ->join('doctors','clinic_doctors.doctor_id','=','doctors.id')
            ->join('doctor_specialities', 'clinic_doctors.doctor_id','=','doctor_specialities.doctor_id')
            ->select(['clinic_doctors.doctor_id','clinic_doctors.clinic_id','doctors.doctor_name','clinics.clinic_name','clinics.lat','clinics.long','doctors.description','doctor_specialities.speciality','doctors.profile_picture','doctors.graduated_since','doctors.vidcall_price','doctors.chat_price','doctors.offline_price','doctors.isonline'])
            ->where('doctor_specialities.speciality',$doctorspeciality)
            ->orderBy('doctors.isonline','desc')->orderBy('doctors.doctor_name',$order)
            // ->orderBy('doctors.vidcall_price',$vidcall)
            ->orderBy('doctors.chat_price',$price)
            // ->orderBy('doctors.offline_price',$onsite)
            ->get();
            return response()->JSON([
                'results' => $query
            ]);
        }

    }

}
