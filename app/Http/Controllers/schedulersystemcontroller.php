<?php

namespace App\Http\Controllers;
use App\Models\orderservice;
use Carbon\Carbon;
use App\Http\Controllers\MobileBannerController;
use App\Http\Controllers\FirebaseTokenController;
use App\Models\vidcalldetail;
use Illuminate\Support\Facades\Http;
use App\Models\wallet;
use App\Http\Controllers\OrderserviceController;
use App\Http\Controllers\MailServer;


use Illuminate\Http\Request;

class schedulersystemcontroller extends Controller
{
    public function __construct(MailServer $mailServer,OrderserviceController $orderService,MobileBannerController $mobile_banner,FirebaseTokenController $fb_token)
    {
        $this->mobile_banner = $mobile_banner;
        $this->fb_token = $fb_token;
        $this->orderService = $orderService;
        $this->mailServer = $mailServer;
    }
    // Schedule for cancel order
    public function orderList(){ 
        $current_timestamp = time();
        $query = orderservice::where('status','like','PENDING_PAYMENT')->where('payed_untill','<',$current_timestamp);
        foreach($query->get() as $data){
            $token_fb = $this->fb_token->userFirebaseToken($query->value('users_ids'),'Consumer App');
            foreach( $token_fb as $token){
                if($token['firebase_token'] != NULL){
                    $notification = $this->mobile_banner->send_notif('Ups.. Your order has been cancel','Your order '.$query->value('order_id').' has been cancelled','','',$token['firebase_token'],NULL,NULL);
                }
            }
            $orderDetail = $this->orderService->orderDetail($data['order_id']);
            $details = [
                'user_detail' =>$orderDetail['results']['user_detail'],
                'order_id' =>$orderDetail['results']['order_id'],
                'service' => $orderDetail['results']['service'],
                'type' => $orderDetail['results']['type'],
                'booking_date' => $orderDetail['results']['booking_date'],
                'total_price' => $orderDetail['results']['total'],
                'total_payment' => $orderDetail['results']['subtotal'],
                'partnerDetail' => $orderDetail['results']['partner_detail'],
            ];
            orderservice::where('order_id','like',$data['order_id'])->update(
                [
                    'status' => 'CANCEL',
                    'cancelled_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'cancelled_reason' => 'Order Not Paid'
                ]
            );
            $this->mailServer->InvoiceCancelCusttomer($details);
        }
    }

    // Schedule for automation close vidcall
    public function vcLinkEnd(){ 
        $current_timestamp = time();
        $query = vidcalldetail::where('status','like','Active')->where('session_done_until','<',$current_timestamp);
        foreach($query->get() as $data){
            $url = env('Whereby_URL')."/".$data['meeting_id'];
            $response = Http::withHeaders([
                'Authorization' => env('Whereby_Token'),
                'Accept' => 'application/json'
            ])->delete($url);
            $query->update(
                [
                    'status' => 'Done',
                    'session_done_time' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
    
    // Schedule for automation give partner payment
    public function paymentPartner(){ 
        $current_timestamp = time();
        $query = orderservice::where('status','like','ORDER_COMPLATE')->where('partner_user_id','!=',NULL)->where('partner_paid_status','=',NULL);
        foreach($query->get() as $data){
            $query2 = wallet::insert([
                'users_ids' => $data['partner_user_id'], 
                'debit' => $data['total'] - $data['comission'],
                'credit' => null,
                'type' => 'partner_wallet',
                'description' => 'Commision from order id '.$data['order_id'],
                'created_at' => Carbon::now()
            ]);
            if($query2 == 1){
                $query->update(
                    [
                        'partner_paid_status' => 'Paid',
                        'partner_paid_ammount' => $data['total'] - $data['comission'],
                        'updated_at' => Carbon::now(),
                        'partner_paid_at' =>  Carbon::now()
                    ]
                );
            }
        }
    }
}
