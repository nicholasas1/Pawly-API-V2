<?php

namespace App\Http\Controllers;
use App\Models\orderservice;
use Carbon\Carbon;
use App\Http\Controllers\MobileBannerController;
use App\Http\Controllers\FirebaseTokenController;

use Illuminate\Http\Request;

class schedulersystemcontroller extends Controller
{
    public function __construct(MobileBannerController $mobile_banner,FirebaseTokenController $fb_token)
    {
        $this->mobile_banner = $mobile_banner;
        $this->fb_token = $fb_token;
    }

    public function orderList(){ 

       
        $current_timestamp = time();
        $query = orderservice::where('status','like','PENDING_PAYMENT')->where('payed_untill','<',$current_timestamp);
        foreach($query->get() as $data){
            $token_fb = $this->fb_token->userFirebaseToken($query->value('users_ids'),'Consumer App');
            foreach( $token_fb as $token){
                $notification = $this->mobile_banner->send_notif('Ups.. Your order has been cancel','Your order '.$query->value('order_id').' has been cancelled','','',$token['firebase_token']);
            }
            $query->update(
                [
                    'status' => 'CANCEL',
                    'cancelled_at' => Carbon::now(),
                    'cancelled_reason' => 'Order Not Paid'
                ]
            );
        }
    }
}
