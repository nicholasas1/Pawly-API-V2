<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clinic;
use App\Models\role;
use App\Models\clinic_doctor;
use App\Models\clinic_facilities;
use App\Models\clinic_op_cl;
use App\Http\Controllers\JWTValidator;
use App\Models\clinic_schedule;
use App\Models\clinic_service;
use App\Models\clinic_schedule_time;
use App\Models\doctor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ratings;
use App\Models\orderservice;
use App\Models\fav;

class ClinicController extends Controller
{
    //
    private $api_key = '';
	protected $JWTValidator;

  public function __construct(JWTValidator $jWTValidator) {
	$this->api_key = env('Maps_API_Key');
	$this->JWTValidator = $jWTValidator;
  }
  

  public function autocomplete(request $request){

        $place = $request->cityname;
		$query = str_replace(' ', '-', $place);
		$location = $request->lattitude.','.$request->longtitude;
    	$apikey = $this->api_key;
    	$url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.$query.'&types=establishment&location='.$location.'&radius=500&key='.$apikey;
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$data1 = curl_exec($ch);
    	curl_close($ch);
    	$details = json_decode($data1, true);

    	foreach($details['predictions'] as $key=>$row) {
    		$arr[] = ['place_id' => $row['place_id'], 'description' => $row['description'], 'main_text' => $row['structured_formatting']['main_text'], 'secondary_text' => $row['structured_formatting']['secondary_text']];
    	}

		$status = $details['status'];
		if($status == 'OK'){
			return response()->JSON([
				'status' => $status,
				'results' => $arr
			]);
		} else{
			return response()->JSON([
				'status' => $status,
				'result' => 'none'
			]);
		}
  
  }
  
  public function getplace(request $request){

  	$apikey = $this->api_key;
  	$latlong = $request->lattitude.','.$request->longtitude;
        $query = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$latlong.'&key='.$apikey;
		;
	    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $query);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
	    $data = curl_exec($ch); // execute curl session
	    curl_close($ch); // close curl session

	$details = json_decode($data, true);
	// foreach($details['results'] as $row) {
	// 	// $placedetail = $row;
	// 	$placedetail[] = ['route' => $row['address_components'][1]['long_name'],'coordinate' => $row['geometry']['location'], 'full_address' =>$row['formatted_address']];
	// }

	// foreach($details['results'] as $row){
	// 	foreach($row['address_components'] as $rows){
	// 		$placedetail[] = ['route' => $rows['long_name']];
	// 	}
	// }

	// foreach($details['results'] as $row){
	// 	foreach($row['address_components'] as $rows){
	// 			$placedetail[] = [
	// 						'route' => $rows['long_name'],
	// 						'cordinate' => $row['geometry']['location'],
	// 						'full_addres' => $row['formatted_address']
	// 			];
	// 		}
	// }
	// foreach($details['results'][0]['address_components'] as $rows){
	// 	// if(in_array("routes", $rows['types'])){
	// 		$placedetail = $rows;
	// 	// }
	// }
	// $placedetail = $details;
	$placedetail = ['route' => $details['results'][0]['address_components'][1]['long_name'], 'coordinate' => $details['results'][0]['geometry']['location'], 'full_address' => $details['results'][0]['formatted_address']];
	$status = $details['status'];

	if($status == 'OK'){
		return response()->JSON([
			'status' => $status,
			'result' => $placedetail
		]);
	} else{
		return response()->JSON([
			'status' => $status,
			'result' => 'none'
		]);
	}
    
  }

  public function getlatlong(request $request){
	$apikey = $this->api_key;
  	$placeid = $request->placeid;
        $query = 'https://maps.googleapis.com/maps/api/geocode/json?place_id='.$placeid.'&key='.$apikey;
		;
	    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $query);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
	    $data = curl_exec($ch); // execute curl session
	    curl_close($ch); // close curl session

	$details = json_decode($data, true);
	foreach($details['results'] as $key=>$row) {
		$placedetail = ['route' => $row['address_components'][1]['long_name'],'coordinate' => $row['geometry']['location'],'full_address' => $row['formatted_address']];
	}
	$status = $details['status'];

	if($status == 'OK'){
		return response()->JSON([
			'status' => $status,
			'result' => $placedetail
		]);
	} else{
		return response()->JSON([
			'status' => $status,
			'result' => 'none'
		]);
	}
  }

	public function addNewClinic(request $request){

		$checkif = clinic::where('user_id',$request->user_id)->get();
		if($checkif->count()>0){
			return response()->JSON([
				'status' => 'error',
				'msg' => 'can only register once'
			]);
		} else{
		$query = clinic::insert([
			'user_id' => $request->user_id,
			'clinic_name' => $request->clinic_name,
			'description' => $request->description,
			'lat' => $request->lat,
			'long' => $request->long,
			'address' => $request->address,
			'clinic_photo' => $request->clinic_photo,
			'worked_since' => $request->worked_since,
		]);
		$clinic_id = clinic::where('user_id',$request->user_id)->value('id');
		$day = ['Sunday', 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
		
		foreach($day as $hari){
			$query2 = clinic_op_cl::insert([
				'clinic_id' => $clinic_id,
				'day' => $hari,
				'opening_hour' => '00:00:00',
				'close_hour' => '23:59:59',
				'status' => 'open'
			]);
		}
		
		if($query==1&&$query2==1){
			$queries = role::insert([
				'userId' => $request->user_id,
				'meta_role' => 'Clinic',
				'meta_id' => $clinic_id
			]);
			$status = "Registration Success";
		} else{
			$status = 'error';
		}
		
		return response()->JSON([
			'status' => $status
		]);
		}
   }

   public function updateclinic(request $request){
	if(filter_var($request->clinic_photo, FILTER_VALIDATE_URL) === FALSE){

		$image_parts = explode(";base64,", $request->profile_picture);
		$image_type_aux = explode("image/", $image_parts[0]);
		$image_type = $image_type_aux[1];
		$image_base64 = base64_decode($image_parts[1]);
		$file = uniqid() . '.'.$image_type;

		file_put_contents(env('Folder_APP').$file, $image_base64);
		$picture = env('IMAGE_URL') . $file;
		
	}else{
		$picture = $request->clinic_photo;
	}
	$query = clinic::where('id',$request->id)->update([
		   'clinic_name' => $request->clinic_name,
		   'description' => $request->description,
		   'lat' => $request->lat,
		   'long' => $request->long,
		   'address' => $request->address,
		   'clinic_photo' => $request->clinic_photo,
		   'worked_since' => $request->worked_since
	]);

	if($query==1){
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

   public function deleteclinic(request $request){
		$delete_clinic_doctor = clinic_doctor::where('clinic_id',$request->clinic_id)->delete();
		$delete_clinic_facilities = clinic_facilities::where('clinic_id',$request->clinic_id)->delete();
		$delete_clinic = clinic::where('id',$request->clinic_id)->delete();
		$delete_service = clinic_service::where('clinic_id',$request->clinic_id)->delete();
		$delete_op_cl = clinic_op_cl::where('clinic_id',$request->clinic_id)->delete();

		if($delete_clinic_doctor==1&&$delete_clinic==1&&$delete_clinic_facilities){
			return response()->JSON([
				'status' => 'success'
			]);
		} else {
			return response()->JSON([
				'status' => 'doctor not found'
			]);
		}
   }

   public function addclinicservices(request $request){
		$query = clinic_service::insert([
			'clinic_id' => $request->clinic_id,
			'service' => $request->service,
			'description' => $request->description,
			'price' => $request->price,
			'status' => $request->status
		]);
		if($query==1){
			return response()->JSON([
				'status' => 'success'
			]);
		} else{
			return response()->JSON([
				'status' => 'error',
				'msg' => ''
			]);
		}
   }

	public function updateclinicservice(request $request){
		$query = clinic_service::where('id',$request->id)->update([
			'clinic_id' => $request->clinic_id,
			'service' => $request->service,
			'description' => $request->description,
			'price' => $request->price,
			'status' => $request->status
		]);
		if($query==1){
			return response()->JSON([
				'status' => 'success'
			]);
		} else{
			return response()->JSON([
				'status' => 'error',
				'msg' => ''
			]);
		}
	}

	public function deleteclinicservices(request $request){
		$query = clinic_service::where('id',$request->id)->delete();
		if($query==1){
			return response()->JSON([
				'status' => 'success'
			]);
		} else{
			return response()->JSON([
				'status' => 'error',
				'msg' => ''
			]);
		}
	}

	public function addopcl(request $request){
		$query = clinic_op_cl::insert([
			'clinic_id' => $request->clinic_id,
			'day' => $request->day,
			'opening_hour' => $request->ophour,
			'close_hour' => $request->clhour,
			'status' => $request->status
		]);

		if($query==1){
			return response()->JSON([
				'status' => 'success'
			]);
		} else{
			return response()->JSON([
				'status' => 'error',
				'msg' => ''
			]);
		}
	}

	public function updateopcl(request $request){
		$query = clinic_op_cl::where('id',$request->id)->update([
			'opening_hour' => $request->ophour,
			'close_hour' => $request->clhour,
			'status' => $request->status
		]);
		if($query==1){
			return response()->JSON([
				'status' => 'success'
			]);
		} else{
			return response()->JSON([
				'status' => 'error',
				'msg' => ''
			]);
		}
	}

	public function deleteopcl(request $request){
		$query = clinic_op_cl::where('clinic',$request->clinic_id)->delete();
		if($query==1){
			return response()->JSON([
				'status' => 'success'
			]);
		} else{
			return response()->JSON([
				'status' => 'error',
				'msg' => ''
			]);
		}
	}

   public function getclinic(request $request){

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

	if($request->order == 'a-z'){
		$order = "clinic_name";
		$order_val = "ASC";
	}else if($request->order == 'z-a'){
		$order = "clinic_name";
		$order_val = "DESC";
	}else if($request->order == 'distance'){
		$order = "distance";
		$order_val = "ASC";
	}else if($request->order == 'lowest_rating'){
		$order = "rating";
		$order_val = "ASC";
	}else if($request->order == 'highest_rating'){
		$order = "rating";
		$order_val = "DESC";
	}else{
		$order = "clinic_name";
		$order_val = "ASC";
	}

	if($request->lat==NULL||$request->long==NULL){
		$lat = "-6.171782389823256";
		$long = "106.82628043498254";
	} else{
		$lat = $request->lat;
		$long = $request->long;
	}

	$today = Carbon::now()->dayName;

	if($request->service==NULL){
		$service = ['grooming','vaksin'];
	} else{
		$service = $request->service;
	}
	
	$clinic = DB::table('clinics')
				->leftjoin('clinic_op_cls','clinics.id','=','clinic_op_cls.clinic_id')
				->leftjoin('clinic_services','clinics.id','=','clinic_services.clinic_id')
				->leftJoin('ratings','clinics.id','=','ratings.clinic_ids')
				->select('clinics.*','clinics.id as clinic_id','clinic_op_cls.*','clinic_services.*','ratings.*','clinic_op_cls.status as open_status','clinic_services.status as servstatus',DB::raw('AVG(ratings.ratings) as rating'), DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"))
				->groupby('clinics.id')
				->where('clinics.clinic_name','like','%'.$request->name.'%')
				->where('clinic_op_cls.day','like',$today)
				->wherein('clinic_services.service',$service)
				->orderBy($order,$order_val);

	

	$count = DB::table('clinics')->leftjoin('clinic_op_cls','clinics.id','=','clinic_op_cls.clinic_id')->leftjoin('clinic_services','clinics.id','=','clinic_services.clinic_id')->leftJoin('ratings','clinics.id','=','ratings.clinic_ids')->select('clinics.*','clinic_op_cls.*','clinic_services.*','clinic_op_cls.status as open_status','clinic_services.status as servstatus','ratings.*', DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"))->where('clinic_op_cls.day','like',$today)->wherein('clinic_services.service',$service)->orderby('distance','asc')->orderBy($order,$order_val)->get();
	$arr = array();
	foreach($clinic->limit($limit)->offset($page)->get() as $queries){
		$totalratings = ratings::where('clinic_ids',$queries->clinic_id)->count();
		$arr[] = [
			'id' => $queries->clinic_id,
			'clinic_name' => $queries->clinic_name,
			'address' => $queries->address,
			'latitude' => $queries->lat,
			'longtitude' => $queries->long,
			'description' => $queries->description,
			'profile_picture' => $queries->clinic_photo,
			'service' => clinic_service::where('clinic_id',$queries->clinic_id)->get(),
			'open_status' => $queries->open_status,
			'opening_hour' => $queries->opening_hour,
			'closing_hour' => $queries->close_hour,
			'service_status' => $queries->servstatus,
			'favourited_by' => fav::where('service_id',$queries->clinic_id)->where('service_meta','clinic')->count(),
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

   	public function filterclinic(request $request){

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

		if($request->order == 'a-z'){
			$order = "clinic_name";
			$order_val = "ASC";
		}else if($request->order == 'z-a'){
			$order = "clinic_name";
			$order_val = "DESC";
		}else if($request->order == 'distance'){
			$order = "distance";
			$order_val = "ASC";
		}else if($request->order == 'lowest_rating'){
            $order = "rating";
            $order_val = "ASC";
        }else if($request->order == 'highest_rating'){
            $order = "rating";
            $order_val = "DESC";
        }else{
			$order = "clinic_name";
			$order_val = "ASC";
		}

		if($request->lat==NULL||$request->long==NULL){
			$lat = "-6.171782389823256";
			$long = "106.82628043498254";
		} else{
			$lat = $request->lat;
			$long = $request->long;
		}

		$today = Carbon::now()->dayName;

		if($request->service==NULL){
			$service = ['grooming','vaksin'];
		} else{
			$service = $request->service;
		}
		
		$clinic = DB::table('clinics')
					->join('clinic_op_cls','clinics.id','=','clinic_op_cls.clinic_id')
					->join('clinic_services','clinics.id','=','clinic_services.clinic_id')
					->leftJoin('ratings','clinics.id','=','ratings.clinic_ids')
					->select('clinics.*','clinic_op_cls.*','clinic_services.*','clinic_op_cls.status as open_status','clinic_services.status as servstatus',DB::raw('AVG(ratings.ratings) as rating'), DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"))
					->where('clinic_op_cls.day','like',$today)
					->wherein('clinic_services.service',$service)
					->orderby('distance','asc')
					->orderBy($order,$order_val);

		$count = DB::table('clinics')->join('clinic_op_cls','clinics.id','=','clinic_op_cls.clinic_id')->join('clinic_services','clinics.id','=','clinic_services.clinic_id')->leftJoin('ratings','clinics.id','=','ratings.clinic_ids')->select('clinics.*','clinic_op_cls.*','clinic_services.*','clinic_op_cls.status as open_status','clinic_services.status as servstatus','ratings.*', DB::raw(" (((acos(sin(('".$lat."'*pi()/180)) * sin((`lat`*pi()/180))+cos(('".$lat."'*pi()/180)) * cos((`lat`*pi()/180)) * cos((('".$long."'- `long`)*pi()/180))))*180/pi())*60*1.1515) AS distance"))->where('clinic_op_cls.day','like',$today)->wherein('clinic_services.service',$service)->orderby('distance','asc')->orderBy($order,$order_val)->get();
		$arr = [];
		$result = [];

		foreach($clinic->limit($limit)->offset($page)->get() as $queries){
			$totalratings = ratings::where('clinic_ids',$queries->id)->count();
			$arr = [
				'id' => $queries->id,
				'clinic_name' => $queries->clinic_name,
				'address' => $queries->address,
				'latitude' => $queries->lat,
				'longtitude' => $queries->long,
				'description' => $queries->description,
				'profile_picture' => $queries->clinic_photo,
				'service' => clinic_service::where('clinic_id',$queries->id)->get(),
				'open_status' => $queries->open_status,
				'opening_hour' => $queries->opening_hour,
				'closing_hour' => $queries->close_hour,
				'service_status' => $queries->servstatus,
				'favourited_by' => fav::where('service_id',$clinic->value('clinics.id'))->where('service_meta','clinic')->count(),
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

	public function getDetail(request $request){
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

		$token = $request->header("Authorization");
		$isfav = '0';
		$query = clinic::leftJoin('ratings','clinics.id','=','ratings.clinic_ids')->select('worked_since','user_id','clinics.id', 'clinics.address','clinic_name','description' , 'clinic_photo' , 'lat', 'clinics.long' , DB::raw('AVG(ratings.ratings) as rating'))->groupBy('clinics.id')->where('clinics.id','=',$request->id);

		if($token!=NULL){
			$result = $this->JWTValidator->validateToken($token);
			if($result['status'] == 200){
				$userid = $result['body']['user_id'];
				$favourited = fav::where('usersids',$userid)->where('service_meta', 'clinic')->where('service_id',$query->value('clinics.id'));
				if($favourited->count()>0){
					$isfav = '1';
				}
			}
		}
		$comision = 12;
		$comision_type = 'percent';
		
		$status = 'error';
		$ratings = ratings::where('clinic_ids',$query->value('doctors.id'));
		if($ratings->count()==0){
			$avgratings = '0.0';
		} else{
			$avgratings = round($ratings->avg('ratings'),1);
		}	

		$year = Carbon::now()->year;
		$dayName = Carbon::now()->dayName;
		
		$clinic_Op_Cl =  clinic_op_cl::where('day',$dayName)->where('clinic_id',$request->id);
		if($clinic_Op_Cl->value('status') == "open"){
			$opening = $clinic_Op_Cl->value('opening_hour');
			$closing = $clinic_Op_Cl->value('close_hour');
		}else{
			$opening = 'tutup';
			$closing = 'tutup';
		}

		return response()->JSON([
			'status' => 'success',
			'results' => [
				'account_id' => $query->value('user_id'),
				'clinic_id' => $query->value('clinics.id'),
				'clinic_name' => $query->value('clinic_name'),
				'description' => $query->value('description'),
				'profile_picture' => $query->value('clinic_photo'),
				'address' => $query->value('address'),
				'worked_since' => $query->value('worked_since'),
				'lat' => $query->value('lat'),
				'long' => $query->value('clinics.long'),
				'opening_hour' => $opening,
				'close_hour' => $closing,
				'clinic_opening_closing_detail' => clinic_op_cl::where('clinic_id',$request->id)->orderByRaw(
					"CASE 
					WHEN Day = 'Sunday' THEN 1 
					WHEN Day = 'Monday' THEN 2
					WHEN Day = 'Tuesday' THEN 3
					WHEN Day = 'Wednesday' THEN 4
					WHEN Day = 'Thursday' THEN 5
					WHEN Day = 'Friday' THEN 6
					WHEN Day = 'Saturday' THEN 7
					END ASC"
			   	)->get(),
				'facility' => clinic_facilities::where('clinic_id',$request->id)->get(),
				'service' => clinic_service::where('clinic_id',$request->id)->get(),
				'favourited_by' => fav::where('service_id',$query->value('clinics.id'))->where('service_meta','clinic')->count(),
				'favourited_by_user' => $isfav,
				'avg_rating' => $avgratings,
				'floor_rating' => floor($query->value('rating')),
				'total_review' => $ratings->count(),
				'review' => ratings::leftJoin('users','ratings.users_id','=','users.id')->where('clinic_ids',$query->value('clinics.id'))->select('ratings.id','clinic_ids','username','profile_picture','reviews','ratings','timereviewed','nickname')->limit($limit)->offset($page)->get(),
				'commision_type' =>  $comision_type,
				'commision_ammount' => $comision
			] 
		]);
	
	}

	public function getDetailSchedule(request $request){
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

		$token = $request->header("Authorization");
		$isfav = '0';
		$year = Carbon::now()->year;
		$query = clinic_schedule::where('clinic_id', $request->id)->where('day',  Carbon::parse($request->date)->dayName);
		
		$result = [];
		foreach($query->limit($limit)->offset($page)->get() as $queries){
			$doctorDetail = doctor::where('id',$queries->doctor_id)->get();
			$orderCheck = orderservice::where('service_id',$request->id)->where('booking_date','LIKE',$request->date);
			$result2 = [];
			foreach(clinic_schedule_time::where('schedule_id',$queries->id)->get() as $ClinicTIme){
					foreach($orderCheck->get() as $orderCheck){	
						if($orderCheck['booking_time'] == $ClinicTIme['start_hour']){
							$arr2 = [
								'id' => $ClinicTIme['id'],
								'schedule_id'  => $ClinicTIme['schedule_id'],
								'start_hour' => $ClinicTIme['start_hour'],
								'end_hour' => $ClinicTIme['end_hour'],
								'can_booking' => false
							];
						}else{
							$arr2 = [
								'id' => $ClinicTIme['id'],
								'schedule_id'  => $ClinicTIme['schedule_id'],
								'start_hour' => $ClinicTIme['start_hour'],
								'end_hour' => $ClinicTIme['end_hour'],
								'can_booking' => true
							];
						}
						array_push($result2,$arr2);
						break;
					}
			}
			$arr = [
				'id' => $queries->id,
				'doctor_id' => $queries->doctor_id,
				'doctor_detail' => [
					'doctor_name' => $doctorDetail[0]['doctor_name'],
					'doctor_profile_picture' => $doctorDetail[0]['profile_picture'],
				],
				'day' => $queries->day,
				'status' => $queries->status,
				'description' => $queries->description,
				'time' => $result2,
			];
			array_push($result,$arr);
		}	

		return response()->JSON([
			'status' => 'success',
			'results' =>  $result
		]);
	
	}

	public function addClinicFacility(request $request){
		$query = clinic_facilities::insert([
			'clinic_id' => $request->clinic_id,
			'facility' => $request->facility
		]);
		if($query==1){
			return response()->JSON([
				'status' => 'success'
			]);
		} else{
			return response()->JSON([
				'status' => 'error',
				'msg' => ''
			]);
		}
   	}

	public function deleteClinicFacility(request $request){
		$query = clinic_facilities::where('id',$request->id)->delete();
		if($query==1){
			return response()->JSON([
				'status' => 'success'
			]);
		} else{
			return response()->JSON([
				'status' => 'error',
				'msg' => ''
			]);
		}
	}


}
