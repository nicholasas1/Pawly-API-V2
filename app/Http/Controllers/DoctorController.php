<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\role;
use App\Models\doctor_speciality;
use App\Models\doctor;
use App\Models\ratings;
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
            'graduated_since' => $request->graduatedsince,
            'graduated_from' => $request->graduatedfrom,
            'worked_since' => $request->workedsince,
            'lat' => $request->lat,
            'long' => $request->long,
            'ratings' => '0',
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
        $totalratings = ratings::where('doctors_ids',$request->id);
        $year = Carbon::now()->year;
        $isonline = [
            'id' => $query->value("id"),
            'doctor_name' => $query->value("doctor_name"),
            'description' => $query->value("description"),
            'profile_picture' => $query->value("profile_picture"),
            'graduated_since' => $query->value("graduated_since"),
            'graduated_from' => $query->value("graduated_from"),
            'experience' => $year-$query->value("worked_since"),
            'latitude' => $query->value('lat'),
            'longtitude' => $query->value('long'),
            'ratings' => $query->value('ratings'),
            'total_review' => $totalratings->count(),
            'chat_price' => $query->value("chat_price"),
            'isonline' => $query->value("isonline"),
            'speciality' => doctor_speciality::where('doctor_id',$query->value('id'))->get(['id','speciality'])
        ];
        $isoffline = [
            'id' => $query->value("id"),
            'doctor_name' => $query->value("doctor_name"),
            'description' => $query->value("description"),
            'profile_picture' => $query->value("profile_picture"),
            'graduated_since' => $query->value("graduated_since"),
            'graduated_from' => $query->value("graduated_from"),
            'experience' => $year-$query->value("worked_since"),
            'latitude' => $query->value('lat'),
            'longtitude' => $query->value('long'),
            'ratings' => $query->value('ratings'),
            'total_review' => $totalratings->count(),
            'chat_price' => $query->value("chat_price"),
            'isonline' => $query->value("isonline"),
            'lastonline' => $query->value("lastonline"),
            'speciality' => doctor_speciality::where('doctor_id',$query->value('id'))->get(['id','speciality'])
        ];
        if($query->count()==1||$query->value('isonline')=='online'){
            return response()->JSON([
                'status' => 'success',
                'results' => $isonline
            ]);
        } else if($query->count()==1||$query->value('isonline')=='offline'){
            return response()->JSON([
                'status' => 'success',
                'results' => $isoffline
                
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
            'graduated_from' => $request->graduatedfrom,
            'worked_since' => $request->workedsince
        ]);

        $doctor = doctor::where('id',$request->id);

        if(doctor::where('id',$request->id)->count()==1){
            $status = "Update Success";
                return response()->JSON([
                    'status' => $status,
                ]);
            } else{
                $status = "Update Failed";
                return response()->JSON([
                    'status' => $status
                ]);
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

            $query = doctor::where('id',$doctorid)->update(['isonline' => 'online']);

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
        if($request->rating=='high'){
            $rating = 'desc';
        } else{
            $rating = 'asc';
        }
        if($request->lat==NULL||$request->long==NULL){
            $lat = "-6.171782389823256";
            $long = "106.82628043498254";
        } else{
            $lat = $request->lat;
            $long = $request->long;
        }
        
        if($request->limit==NULL){
            $limit = 10;
        } else{
            $limit = $request->limit;
        }

        if($request->pages==NULL){
            $page = 0;
        } else{
            $page = $request->pages;
        }
        $arr[] = ['null'];
        if($doctorspeciality==NULL){

            
            $query = DB::table('doctors')
            // ->join('clinics','clinic_doctors.clinic_id','=','clinics.id')
            // ->join('doctors','clinic_doctors.doctor_id','=','doctors.id')
            // ->join('doctor_specialities', 'doctors.id','=','doctor_specialities.doctor_id')
            ->select(['doctors.id','doctors.doctor_name','doctors.lat','doctors.long',DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"),'doctors.description','doctors.profile_picture','doctors.worked_since','doctors.graduated_since','doctors.vidcall_price','doctors.chat_price','doctors.offline_price','doctors.isonline','doctors.ratings'])
            ->having('distance','<','22')
            ->orderBy('doctors.isonline','desc')
            ->orderBy('distance', 'asc')
            ->orderBy('doctors.doctor_name',$order)
            ->orderBy('doctors.ratings', $rating)
            // ->orderBy('doctors.vidcall_price',$vidcall)
            ->orderBy('doctors.chat_price',$price)
            ->limit($limit)
            ->offset($page)
            // ->orderBy('doctors.offline_price',$onsite)
            ->get();
            
            foreach($query as $queries){
                $speciality = DB::table('doctor_specialities')->where('doctor_id',$queries->id)->select(['id','speciality'])->get();
                $year = Carbon::now()->year;
                $totalratings = ratings::where('doctors_ids',$queries->id)->count();
                $arr[] = [
                    'id' => $queries->id,
                    'doctor_name' => $queries->doctor_name,
                    'latitude' => $queries->lat,
                    'longtitude' => $queries->long,
                    'distance' => $queries->distance,
                    'description' => $queries->description,
                    'profile_picture' => $queries->profile_picture,
                    'graduated_since' => $queries->graduated_since,
                    'experience' => $year-$queries->worked_since,
                    'speciality' => $speciality,
                    'chat_price' => $queries->chat_price,
                    'isonline' => $queries->isonline,
                    'ratings' => $queries->ratings,
                    'total_review' => $totalratings,
                ];
         }

            return response()->JSON([
                'status' => 'success',
                'total_data' => $query->count(),
                'results' => $arr
            ]);

        } else{
            $query = DB::table('doctors')
            // ->join('clinics','clinic_doctors.clinic_id','=','clinics.id')
            // ->join('doctors','clinic_doctors.doctor_id','=','doctors.id')
            ->join('doctor_specialities', 'doctors.id','=','doctor_specialities.doctor_id')
            ->select(['doctors.id','doctors.doctor_name','doctors.lat','doctors.long',DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"),'doctors.description','doctor_specialities.speciality','doctors.profile_picture','doctors.worked_since','doctors.graduated_since','doctors.vidcall_price','doctors.chat_price','doctors.offline_price','doctors.isonline','doctors.ratings'])
            ->having('distance','<','22')
            ->where('doctor_specialities.speciality',$doctorspeciality)
            ->orderBy('doctors.isonline','desc')
            ->orderBy('doctors.doctor_name',$order)
            ->orderBy('doctors.ratings', $rating)
            // ->orderBy('doctors.vidcall_price',$vidcall)
            ->orderBy('doctors.chat_price',$price)
            ->limit($limit)
            ->offset($page)
            // ->orderBy('doctors.offline_price',$onsite)
            ->get();

            foreach($query as $queries){
                $year = Carbon::now()->year;
                $totalratings = ratings::where('doctors_ids',$queries->id)->count();
                $arr[] = [
                    'id' => $queries->id,
                    'doctor_name' => $queries->doctor_name,
                    'latitude' => $queries->lat,
                    'longtitude' => $queries->long,
                    'distance' => $queries->distance,
                    'description' => $queries->description,
                    'profile_picture' => $queries->profile_picture,
                    'graduated_since' => $queries->graduated_since,
                    'experience' => $year-$queries->worked_since,
                    'speciality' => $queries->speciality,
                    'chat_price' => $queries->chat_price,
                    'isonline' => $queries->isonline,
                    'ratings' => $queries->ratings,
                    'total_review' => $totalratings,
                ];
         }

            return response()->JSON([
                'status' => 'success',
                'total_data' => $query->count(),
                'results' => $arr
            ]);
        }

    }

}
