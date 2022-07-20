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
    	$json =  "{\"results\": [";
    	foreach($details['predictions'] as $key=>$row) {
    		$arr[] = "{\"id\": \"".$row['reference']."\", \"value\": \"".$row['description']."\"}";
    	}
    	$json .= implode(", ", $arr);
    	echo $json . "]}";
  
  }
  
  public function get_geocode(){

  	$apikey = $this->api_key;
  	$reference = $_GET["reference"];
        $query = 'https://maps.googleapis.com/maps/api/place/details/json?reference='.urlencode($reference).'&sensor=true&key='.$apikey;
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
