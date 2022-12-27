<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class socket_notf extends Controller
{
    public function update_order($order_id,$status,$user_id,$partner_user_id){
        $url = 'https://socket-pawly.onrender.com/newOrder?order_id='.$order_id.'&status='.$status.'&id_user='.$user_id.'&partner_user_id='.$partner_user_id;
        
        $response = Http::get($url);

        return $response->json();
    }
}
