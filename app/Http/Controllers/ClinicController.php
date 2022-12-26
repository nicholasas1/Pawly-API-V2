<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clinic;
use App\Models\role;
use App\Models\clinic_doctor;
use App\Models\clinic_facilities;
use App\Http\Controllers\JWTValidator;
use App\Models\clinic_service;

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

	$query = clinic::insert([
		   'user_id' => $request->user_id,
		   'clinic_name' => $request->clinic_name,
		   'description' => $request->description,
		   'lat' => $request->lat,
		   'long' => $request->long,
		   'address' => $request->address,
		   'clinic_photo' => $request->clinic_photo,
		   'opening_hour' => $request->opening_hour,
	   ]);

	   $clinic_id = clinic::where('user_id',$request->user_id)->value('id');
	   if($query==1){
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

   public function update_clinic(request $request){
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
		   'opening_hour' => $request->opening_hour,
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
			'clinic_id' => $request->id,
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
		'clinic_id' => $request->id,
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

	// $token = $request->header("Authorization");
	// $result = $this->JWTValidator->validateToken($token);
	// $userid = $result['body']['user_id'];

	$query = clinic::leftjoin('clinic_doctors','clinics.id','=','clinic_doctors.clinic_id')
			->select('clinic_doctors.*','clinics.*');
		
	$arr = [
		'id' => $query->value('clinics.id'),
		'clinic_name' => $query->value('clinics.clinic_name'),
		'address' => $query->value('clinics.address'),
		'longtitude' => $query->value('long'),
		'latitude' => $query->value('lat'),
		'description' => $query->value('description'),
		'photo_profile' => $query->value('clinic_photo'),
		'opening_hour' => $query->value('opening_hour'),
		'close_hour' => $query->value('close_hour'),
	];

	return response()->JSON([
		'status' => 'success',
		'results' => $arr
	]);
   }
}
