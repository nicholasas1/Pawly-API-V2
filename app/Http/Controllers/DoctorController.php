<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\role;
use App\Models\doctor_speciality;
use App\Models\doctor;
use App\Models\clinic;
use App\Models\ratings;
use App\Models\fav;
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
        if(filter_var($request->profile_picture, FILTER_VALIDATE_URL) === FALSE){

            $image_parts = explode(";base64,", $request->profile_picture);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = uniqid() . '.'.$image_type;
    
            file_put_contents(env('Folder_APP').$file, $image_base64);
            $picture = env('IMAGE_URL') . $file;
            
        }else{
            $picture = $request->profile_picture;
        }

     $query = doctor::insert([
            'users_ids' => $request->id,
            'doctor_name' => $request->name,
            'description' => $request->description,
            'Biography' => $request->biography,
            'address' => $request->address,
            'Education_experience' => $request->educational_experience,
            'profile_picture' => $picture,
            'graduated_since' => $request->graduatedsince,
            'graduated_from' => $request->graduatedfrom,
            'worked_since' => $request->workedsince,
            'lat' => $request->lat,
            'long' => $request->long,
            'isonline' => 'online'
        ]);

        $doctorid = doctor::where('users_ids',$request->id)->value('id');
        doctor_speciality::insert([
            'doctor_id' => $doctorid ,
            'speciality' => "umum"
        ]);
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
        if($request->limit==NULL){
            $limit = 10;
        } else{
            $limit = $request->limit;
        }

        if($request->page==NULL){
            $page = 0;
        } else{
            $page = $request->page - 1 * $limit;
        }

        $token = $request->header("Authorization");
        $isfav = '0';
        $query = doctor::leftJoin('ratings','doctors.id','=','ratings.doctors_ids')->select('users_ids','doctors.id', 'doctors.address','doctor_name','description' , 'profile_picture' ,'graduated_from', 'graduated_since' , 'worked_since' , 'lat', 'doctors.long','vidcall_price' , 'chat_price', 'offline_price', 'isonline' , 'lastonline','Biography','Education_experience','vidcall_available','chat_available','offline_available', DB::raw('AVG(ratings.ratings) as rating'))->groupBy('doctors.id')->where('doctors.id','=',$request->id);

        if($token!=NULL){
            $result = $this->JWTValidator->validateToken($token);
            if($result['status'] == 200){
                $userid = $result['body']['user_id'];
                $favourited = fav::where('usersids',$userid)->where('service_meta', 'doctor')->where('service_id',$query->value('doctors.id'));
                if($favourited->count()>0){
                    $isfav = '1';
                }
            }
        }
        $comision = null;
        $comision_type = null;
        
        $status = 'error';
        $ratings = ratings::where('doctors_ids',$query->value('doctors.id'));
        if($ratings->count()==0){
            $avgratings = '0.0';
        } else{
            $avgratings = round($ratings->avg('ratings'),1);
        }
        if($request->service == 'chat'){
            $comision = 6000;
            $comision_type = 'fixed';
        }else if($request->service == 'vidcall'){
            $comision = 6000;
            $comision_type = 'fixed';
        }else if($request->service == 'onsite'){
            $comision = 12;
            $comision_type = 'percent';
        }

        $year = Carbon::now()->year;
        return response()->JSON([
            'status' => 'success',
            'results' => [
                'account_id' => $query->value('users_ids'),
                'doctor_id' => $query->value('doctors.id'),
                'doctor_name' => $query->value('doctor_name'),
                'description' => $query->value('description'),
                'profile_picture' => $query->value('profile_picture'),
                'Biography' => $query->value('Biography'),
                'address' => $query->value('address'),
                'Education_experience' => $query->value('Education_experience'),
                'worked_since' => $query->value('worked_since'),
                'graduated_from' => $query->value('graduated_from'),
                'graduated_since' => $query->value('graduated_since'),
                'experience' => $year-$query->value('worked_since'),
                'lat' => $query->value('lat'),
                'long' => $query->value('doctors.long'),
                'vidcall_available' => $query->value('vidcall_available'),
                'vidcall_price' => $query->value('vidcall_price'),
                'chat_available' => $query->value('chat_available'),
                'chat_price' => $query->value('chat_price'),
                'offline_available' => $query->value('offline_available'),
                'offline_price' => $query->value('offline_price'),
                'isonline' => $query->value('isonline'),
                'lastonline' => $query->value('lastonline'),
                'favourited_by' => fav::where('service_id',$query->value('doctors.id'))->where('service_meta','doctor')->count(),
                'favourited_by_user' => $isfav,
                'avg_rating' => $avgratings,
                'floor_rating' => floor($query->value('rating')),
                'total_review' => $ratings->count(),
                'review' => ratings::leftJoin('users','ratings.users_id','=','users.id')->where('doctors_ids',$query->value('doctors.id'))->select('ratings.id','doctors_ids','username','profile_picture','reviews','ratings','timereviewed','nickname')->limit($limit)->offset($page)->get(),
                'working_at' => clinic_doctor::where('doctor_id',$query->value('doctors.id'))->leftJoin('clinics','clinics.id','=','clinic_id')->get(),
                'speciality' => doctor_speciality::where('doctor_id',$query->value('doctors.id'))->get(),
                'commision_type' => $comision,
                'commision_ammount' => $comision_type
            ] 
        ]);
        
    }

    public function updatedoctor(request $request){
        if(filter_var($request->profile_picture, FILTER_VALIDATE_URL) === FALSE){

            $image_parts = explode(";base64,", $request->profile_picture);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = uniqid() . '.'.$image_type;
    
            file_put_contents(env('Folder_APP').$file, $image_base64);
            $picture = env('IMAGE_URL') . $file;
            
        }else{
            $picture = $request->profile_picture;
        }

        $query = doctor::where('id',$request->id)->update([
            'doctor_name' => $request->name,
            'description' => $request->description,
            'Biography' => $request->biography,
            'address' => $request->address,
            'Education_experience' => $request->educational_experience,
            'profile_picture' => $picture,
            'graduated_since' => $request->graduatedsince,
            'graduated_from' => $request->graduatedfrom,
            'worked_since' => $request->workedsince,
            'lat' => $request->lat,
            'long' => $request->long,
            'vidcall_available' => $request->vidcall_status,
            'vidcall_price' => $request->vidcall_price,
            'chat_available' => $request->chat_status,
            'chat_price' => $request->chat_price,
            'offline_available' => $request->offline_status,
            'offline_price' => $request->offline_price
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

        $query = doctor_speciality::where('id',$request->id)->delete();
        if($query == 1){
            $status = "success";
        }else{
            $status = "error";
        }
        return response()->json([
            'status'=>$status,
        ]); 

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
        } else if ($request->order=='a-z'){
            $order = 'asc';
        }else{
            $order = 'asc';
        }
        
        if($request->price=='high'){
            $price = 'desc';
        } else if($request->price=='low'){
            $price = 'asc';
        }else{
            $price = 'asc';
        }

        if($request->rating=='high'){
            $rating = 'desc';
        }else if($request->rating=='low'){
            $rating = 'asc';
        }else{
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
            $page = $request->pages - 1 * $limit;
        }
        $arr = NULL;
        if($doctorspeciality==NULL){

            
            $query = DB::table('doctors')
            // ->join('clinics','clinic_doctors.clinic_id','=','clinics.id')
            // ->join('doctors','clinic_doctors.doctor_id','=','doctors.id')
            // ->join('doctor_specialities', 'doctors.id','=','doctor_specialities.doctor_id')
            ->select(['doctors.id','doctors.doctor_name','doctors.lat','doctors.long',DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"),'doctors.description','doctors.profile_picture','doctors.vidcall_price','doctors.offline_price','doctors.worked_since','doctors.graduated_since','doctors.vidcall_price','doctors.chat_price','doctors.offline_price','doctors.isonline','doctors.ratings'])
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
            
            $totaldata = $query->count();

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
                    'experience' => $year - $queries->worked_since,
                    'speciality' => $speciality,
                    'chat_price' => $queries->chat_price,
                    'vidcall_price' => $queries->vidcall_price,
                    'offline_price' => $queries->offline_price,
                    'isonline' => $queries->isonline,
                    'ratings' => $queries->ratings,
                    'floor_rating' => floor($queries->ratings),
                    'total_review' => $totalratings,
                ];
         }
            return response()->JSON([
                'status' => 'success',
                'total_data' => $query->count(),
                'pages' => ceil($query->count()/$limit),
                'results' => $arr
            ]);

        } else{
            $query = DB::table('doctors')
            // ->join('clinics','clinic_doctors.clinic_id','=','clinics.id')
            // ->join('doctors','clinic_doctors.doctor_id','=','doctors.id')
            ->join('doctor_specialities', 'doctors.id','=','doctor_specialities.doctor_id')
            ->select(['doctors.id','doctors.doctor_name','doctors.lat','doctors.long',DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"),'doctors.description','doctor_specialities.speciality','doctors.profile_picture','doctors.vidcall_price','doctors.offline_price','doctors.worked_since','doctors.graduated_since','doctors.vidcall_price','doctors.chat_price','doctors.offline_price','doctors.isonline','doctors.ratings'])
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
                    'graduated_since' => $year-$queries->graduated_since,
                    'experience' => $year-$queries->worked_since,
                    'speciality' => $queries->speciality,
                    'chat_price' => $queries->chat_price,
                    'vidcall_price' => $queries->vidcall_price,
                    'offline_price' => $queries->offline_price,
                    'isonline' => $queries->isonline,
                    'ratings' => $queries->ratings,
                    'floor_rating' => floor($queries->ratings),
                    'total_review' => $totalratings,
                ];
         }
         if($arr = NULL){
            $arr[] = ['null'];
         }
            return response()->JSON([
                'status' => 'success',
                'total_data' => $query->count(),
                'pages' => ceil($query->count()/$limit),
                'results' => $arr
            ]);
        }

    }
    
    public function doctorGetList(request $request){
        if($request->lat==NULL||$request->long==NULL){
            $lat = "-6.171782389823256";
            $long = "106.82628043498254";
        } else{
            $lat = $request->lat;
            $long = $request->long;
        }

        if($request->speciality != NULL){
            $speciality = $request->speciality;
        }else{
            $speciality = NULL;
        }

        if($request->order == 'a-z'){
            $order = "doctor_name";
            $order_val = "ASC";
        }else if($request->order == 'z-a'){
            $order = "doctor_name";
            $order_val = "DESC";
        }else if($request->order == 'lowest_price'){
            $order = "vidcall_price";
            $order_val = "ASC";
        }else if($request->order == 'highest_price'){
            $order = "vidcall_price";
            $order_val = "DESC";
        }else if($request->order == 'lowest_rating'){
            $order = "rating";
            $order_val = "ASC";
        }else if($request->order == 'highest_rating'){
            $order = "rating";
            $order_val = "DESC";
        }else if($request->order == 'distance'){
            $order = "distance";
            $order_val = "ASC";
        }else{
            $order = "doctor_name";
            $order_val = "ASC";
        }

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
        
        $query = DB::table('doctors')
                ->leftJoin('doctor_specialities','doctors.id','=','doctor_specialities.doctor_id')
                ->leftJoin('ratings','doctors.id','=','ratings.doctors_ids')
                ->select('doctors.id', 'doctor_name','description' , 'profile_picture' , 'graduated_since' , 'worked_since' , 'lat', 'doctors.long','vidcall_price' , 'chat_price', 'offline_price', 'isonline' , 'lastonline', DB::raw('AVG(ratings.ratings) as rating'), DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"))
                ->where('speciality','LIKE','%'.$speciality.'%')->where('doctor_name','LIKE','%'.$request->name.'%')
                ->groupBy('doctors.id')
                ->orderBy('isonline','DESC')
                ->orderBy($order,$order_val);
        
        $count = DB::table('doctors')->leftJoin('doctor_specialities','doctors.id','=','doctor_specialities.doctor_id')->leftJoin('ratings','doctors.id','=','ratings.doctors_ids')->select('doctors.id', 'doctor_name','description' , 'profile_picture' , 'graduated_since' , 'worked_since' , 'lat', 'doctors.long','vidcall_price' , 'chat_price', 'offline_price', 'isonline' , 'lastonline', DB::raw('AVG(ratings.ratings) as rating'), DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"))->where('speciality','LIKE','%'.$speciality.'%')->groupBy('doctors.id')->orderBy('isonline','DESC')->orderBy($order,$order_val)->get();
        $arr = array();
        foreach($query->limit($limit)->offset($page)->get() as $queries){
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
                'graduated_since' => $year-$queries->graduated_since,
                'experience' => $year-$queries->worked_since,
                'speciality' => doctor_speciality::where('doctor_id',$queries->id)->get(),
                'chat_price' => $queries->chat_price,
                'vidcall_price' => $queries->vidcall_price,
                'offline_price' => $queries->offline_price,
                'isonline' => $queries->isonline,
                'favourited_by' => fav::where('service_id',$query->value('doctors.id'))->where('service_meta','doctor')->count(),
                'ratings' => $queries->rating,
                'floor_rating' => floor($queries->rating),
                'total_review' => $totalratings,
            ];
        }
        if($arr == NULL){
            $msg = "Data not found";
        }else{
            $msg = "";
        }

        return response()->JSON([
            'status' => 'success',
            'msg' => $msg,
            'total_data' => count($count),
            'total_page' => ceil(count($count) / $limit),
            'total_result' => count($arr),
            'results' => $arr
        ]);

    }
}
