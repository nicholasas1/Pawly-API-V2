<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class whatsapp_notif extends Controller
{
    public function sendWaText($phone_number, $text){
        $url = 'https://whapi.io/api/send';
        $timestamp = Carbon::now()->timestamp;
        $data = array(
            'app' => [
                'id'=> '6285717105056',
                'time'=>  $timestamp,
                "data"=>[
                    "recipient"=>[
                        "id"=> $phone_number,
                    ],
                    'message' => [
                        [
                            "time"=>  $timestamp,
                            "type"=>"text",
                            "value"=> $text
                        ],
                    ],  
                ]
            ]  
         );

        $response = Http::post($url, $data);

        return $response->json();
      
    }
}
