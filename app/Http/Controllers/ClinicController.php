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
    		$arr[] = ['place_id' => $row['place_id'], 'Description' => $row['description']];
    	}

		$status = $details['status'];
		if($status == 'OK'){
			return response()->JSON([
				'status' => $status,
				'results' =>array([
					'place' => $arr
				])
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

	$status = $details['status'];

	if($status == 'OK'){
		return response()->JSON([
			'status' => $status,
			'result' => array([
				'place' => $details
			])
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

	$status = $details['status'];

	if($status == 'OK'){
		return response()->JSON([
			'status' => $status,
			'result' => array([
				'place' => $details
			])
		]);
	} else{
		return response()->JSON([
			'status' => $status,
			'result' => 'none'
		]);
	}
  }
}
