<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClinicController extends Controller
{
    //
    private $api_key = '';


  function __construct() {
	$this->api_key = env('Maps_API_Key');
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
		$placedetail = ['route' => $row['address_components'][1]['long_name'],'address' => $row['formatted_address'],'coordinate' => $row['geometry']['location']];
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
}
