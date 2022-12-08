<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class socket_notf extends Controller
{
    public function update_order($order_id,$status,$user_id,$partner_user_id){
        $url = 'https://socket-pawly.onrender.com/newOrder?order_id=2wscd&status=pending_payment&id_user=1&partner_user_id=1';
        
        $response = Http::get($url);

        return $response->json();
    }
}
