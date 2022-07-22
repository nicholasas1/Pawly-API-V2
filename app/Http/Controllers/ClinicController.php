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
    
        header("Cache-Control: private, max-age=86400");
	    header("Expires: ".gmdate('r', time()+86400));
        $query = $request->cityname;
		$location = $request->lattitude.','.$request->longtitude;
    	$apikey = $this->api_key;
    	$url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input='.$query.'&types=establishment&location='.$location.'&radius=500&key='.$apikey;
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	$data1 = curl_exec($ch);
    	curl_close($ch);
    	$details = json_decode($data1, true);
    	header("Content-Type: application/json");
    	foreach($details['predictions'] as $key=>$row) {
    		$arr[] = "[".$row['description']."]";
    	}

		return response()->JSON([
			'status' => 'success',
			'results' =>array([
				'place' => $arr
			])
		]);
  
  }
  
  public function get_geocode(request $request){

  	$apikey = $this->api_key;
  	$latlong = $request->lattitude.','.$request->longtitude;
        $query = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$latlong.'&key='.$apikey;
	    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $query);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
	    $data = curl_exec($ch); // execute curl session
	    curl_close($ch); // close curl session

	$details = json_decode($data, true);
	$map['lat'] = $details['result']['geometry']['location']['lat'];
	$map['long'] = $details['result']['geometry']['location']['lng'];
	
    return json_encode($map);
  }

}
