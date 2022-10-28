<?php

namespace App\Http\Controllers;

use App\Models\orderservice;
use App\Models\couponusages;
use App\Models\couponservice;
use Illuminate\Http\Request;
use App\Http\Controllers\CouponserviceController;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;
use App\Http\Controllers\JWTValidator;
use App\Http\Controllers\WalletController;
use Carbon\Carbon;
use App\Models\doctor;
use App\Models\clinic;
use App\Models\wallet;
use App\Models\vidcalldetail;
use Illuminate\Support\Facades\Http;
use Symfony\Component\VarDumper\VarDumper;
use App\Models\User;
use App\Http\Controllers\FirebaseTokenController;
use App\Http\Controllers\MobileBannerController;



class OrderserviceController extends Controller
{
    protected $coupons;
    protected $JWTValidator;
    public function __construct(WalletController $wallet,CouponserviceController $coupons, JWTValidator $jWTValidator,FirebaseTokenController $fb_token,MobileBannerController $mobile_banner)
    {
        $this->coupons = $coupons;
        $this->JWTValidator = $jWTValidator;
        $this->fb_token = $fb_token;
        $this->mobile_banner = $mobile_banner;
        $this->wallet = $wallet;
    }

    public function order_service(request $request){

        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);

        if($result['status'] == 200){
            $service = $request->service;
            $price = $request->price;
            $coupon_name = $request->coupon;
            $service_id = $request->servid;
            $pet_id = $request->pet_id;
            $type = $request->type;
            $partner_user_id = $request->partner_user_id;
            $booking_time = $request->booking_time;
            if($request->partner_commision_type == 'fixed'){
                $comission = $request->comission;
            }else{
                $comission = $price * $request->comission/100;
            }
            $userid = $result['body']['user_id'];
            $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            if($service=='chat'){
                $ordercode = 'CHT';
                $paid_until = time()+ 3600;
            } else if($service=='vidcall'){
                $ordercode = 'VDC';
                $paid_until = time()+ 3600;
            } else if($service=='onsite'){
                $ordercode = 'DOS';
                $dateformat = date($booking_time);
                $paid_until = strtotime($dateformat) - 3600*2;
            } else if($service=='pawly_credit'){
                $ordercode = 'PWC';
                $paid_until = time()+ 3600*24;
            } else{
                $paid_until = time()+ 3600*24;
            }

        
            if($coupon_name==NULL){
                $total_price = $price;
                $discount = 0;
                $subtotal = $total_price-$discount;
                $query = orderservice::insertGetId([
                    'service' => $service,
                    'service_id' => $service_id,
                    'type' => $type,
                    'pet_id' => $pet_id,
                    'users_ids' => $userid,
                    'status' => 'PENDING_PAYMENT',
                    'total' => $total_price,
                    'diskon' => $discount,
                    'subtotal' => $subtotal,
                    'created_at' => Carbon::now(),
                    'partner_user_id' => $partner_user_id,
                    'comission' => $comission,
                    'payed_untill' => $paid_until,
                    'booking_date' => $booking_time
                ]);
                $insertorderid = orderservice::where('id',$query)->update([
                    'order_id' => $ordercode.substr(str_shuffle(str_repeat($pool, 5)), 0, 3).$query
                ]);

                if($insertorderid==1){
                    return response()->JSON([
                        'status' => 'success',
                        'results' => orderservice::where('id',$query)->get()
                    ]);
                } else{
                    return response()->JSON([
                        'status' => 'error',
                        'status' => ''
                    ]);
                }
                
            } else{
                $coupons_respond = $this->coupons->coupon_service($coupon_name,$userid,$service,$price);
              
                if($coupons_respond['status']=='success'){
                    $total_price = $price;
                    $discount = $coupons_respond['value'];
                    $subtotal = $total_price-$discount;
                    $query = orderservice::insertGetId([
                    'service' => $service,
                    'service_id' => $service_id,
                    'type' => $type,
                    'pet_id' => $pet_id,
                    'status' => 'PENDING_PAYMENT',
                    'users_ids' => $userid,
                    'coupon_name' => $coupon_name,
                    'total' => $total_price,
                    'diskon' => $discount,
                    'subtotal' => $subtotal,
                    'created_at' => Carbon::now(),
                    'partner_user_id' => $partner_user_id,
                    'comission' => $comission,
                    'payed_untill' => $paid_until,
                    'booking_date' => $booking_time
                ]);
               $orderId = $ordercode.substr(str_shuffle(str_repeat($pool, 5)), 0, 8).$query;
                $insertorderid = orderservice::where('id',$query)->update([
                    'order_id' => $orderId
                ]);
                $query2 = couponusages::insert([
                    'coupon_name' => $coupon_name,
                    'user_id' => $userid,
                    'service' => $service,
                    'type' => $type,
                    'date' => Carbon::today()->toDateString(),
                    'order_id' => $orderId
                ]);

                if($insertorderid==1){
                    return response()->JSON([
                        'status' => 'success',
                        'results' => orderservice::where('id',$query)->get()
                    ]);
                } else{
                    return response()->JSON([
                        'status' => 'error',
                        'msg' => ''
                    ]);
                }
                } else{
                    
                    return response()->JSON([
                        'status' => 'error',
                        'msg' => 'coupon_can_not_be_used'
                    ]);
                
                }
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'no_user_found'
            ]);
        }
    }

    public function orderList(Request $request){
        $orderId = $request->orderId;
        $type = $request->type;
        $service = $request->service;
        $status = $request->status;
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
        

        $data = orderservice::where('order_id','like','%'.$orderId.'%')->where('type','like','%'.$type.'%')->where('service','like','%'.$service.'%')->where('status','like','%'.$status.'%');;
        $result=[];
        foreach($data->limit($limit)->offset($page)->get() as $arr){
            if($data->value('coupon_name')==NULL){
                $payment_allowed = 'a:2:{i:0;s:4:"dana";i:1;s:3:"ovo";}';
            } else{
                $payment_allowed = couponservice::where('coupon_name',$data->value('coupon_name'))->value('allowed_payment');
            }
            $method = array(
                'id' => $arr['id'],
                'order_id'=>$arr['order_id'],
                'service'=>$arr['service'],
                'service_id'=>$arr['service_id'],
                'pet_id'=>$arr['pet_id'],
                'type'=>$arr['type'],
                'status'=>$arr['status'],
                'total'=>$arr['total'],
                'diskon'=>$arr['diskon'],
                'coupon_name'=>$arr['coupon_name'],
                'subtotal'=>$arr['subtotal'],
                'allowed_payment'=>$payment_allowed,
                'payment_method'=>$arr['payment_method'],
                'payment_id'=>$arr['payment_id'],
                'booking_date'=>$arr['booking_date'],
                'payed_at'=>$arr['payed_at'],
                'payed_untill'=>$arr['payed_untill'],
                'cancelled_at'=>$arr['cancelled_at'],
                'cancelled_reason'=>$arr['cancelled_reason'],
                'users_ids'=>$arr['users_ids'],
                'partner_user_id'=>$arr['partner_user_id'],
                'comission'=>$arr['comission'],
                'partner_paid_status'=>$arr['partner_paid_status'],
                'partner_paid_ammount'=>$arr['partner_paid_ammount'],
                'partner_paid_at'=>$arr['partner_paid_at'],
                'refund_at'=>$arr['refund_at'],
                'created_at'=>$arr['created_at'],
                'updated_at'=>$arr['updated_at']
            );
            array_push($result, $method);
        }

        return response()->json([
            'status'=>'success',  
            'total_data'=>$data->count(),  
            'total_page'=> ceil($data->count() / $limit),
            'results'=>$result
        ]);
    }

    public function orderListToken(Request $request){
        $orderId = $request->orderId;
        $type = $request->type;
        $service = $request->service;
        $status = $request->status;
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
       
        
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);

        if($result['status'] == 200){
            $data = orderservice::where('users_ids','like', $result['body']['user_id'])->where('order_id','like','%'.$orderId.'%')->where('type','like','%'.$type.'%')->where('service','like','%'.$service.'%')->where('status','like','%'.$status.'%') ->orderBy('created_at','DESC');
            $result=[];
            
            foreach($data->limit($limit)->offset($page)->get() as $arr){
                if($arr['coupon_name']==NULL){
                    $payment_allowed = 'a:2:{i:0;s:4:"dana";i:1;s:3:"ovo";}';
                } else{
                    $payment_allowed = couponservice::where('coupon_name',$data->value('coupon_name'))->value('allowed_payment');
                }
                $method = array(
                    'id' => $arr['id'],
                    'order_id'=>$arr['order_id'],
                    'service'=>$arr['service'],
                    'service_id'=>$arr['service_id'],
                    'pet_id'=>$arr['pet_id'],
                    'type'=>$arr['type'],
                    'status'=>$arr['status'],
                    'total'=>$arr['total'],
                    'diskon'=>$arr['diskon'],
                    'coupon_name'=>$arr['coupon_name'],
                    'subtotal'=>$arr['subtotal'],
                    'allowed_payment'=>$payment_allowed,
                    'payment_method'=>$arr['payment_method'],
                    'payment_id'=>$arr['payment_id'],
                    'booking_date'=>$arr['booking_date'],
                    'payed_at'=>$arr['payed_at'],
                    'payed_untill'=>$arr['payed_untill'],
                    'cancelled_at'=>$arr['cancelled_at'],
                    'cancelled_reason'=>$arr['cancelled_reason'],
                    'users_ids'=>$arr['users_ids'],
                    'partner_user_id'=>$arr['partner_user_id'],
                    'comission'=>$arr['comission'],
                    'partner_paid_status'=>$arr['partner_paid_status'],
                    'partner_paid_ammount'=>$arr['partner_paid_ammount'],
                    'partner_paid_at'=>$arr['partner_paid_at'],
                    'refund_at'=>$arr['refund_at'],
                    'created_at'=>$arr['created_at'],
                    'updated_at'=>$arr['updated_at']

                );
                array_push($result, $method);
            }
            return response()->json([
                'status'=>'success',  
                'total_data'=>$data->count(),  
                'total_page'=> ceil($data->count() / $limit),
                'results'=>$result
            ]);
        }else{
            return $result;
        }     
    }

    public function getDetail(request $request)
    {
        $orderId = $request->id;
        
        $data = orderservice::where('order_id','like',$orderId);
        
        if($data->value('type') == 'doctor'){
            $detail = doctor::where('id','like',$data->value('service_id'));
            $res = [
                'account_id' => $detail->value('users_ids'),
                'doctor_id'=>$detail->value('id'),
                'doctor_name'=>$detail->value('doctor_name'),
                'profile_picture'=>$detail->value('profile_picture'),
            ];
        }

        if($data->value('coupon_name')==NULL){
            $payment_allowed = '';
        } else{
            $payment_allowed =  couponservice::where('coupon_name',$data->value('coupon_name'))->value('allowed_payment');
        }

        $arr = [
            'id' => $data->value('id'),
            'order_id'=>$data->value('order_id'),
            'service'=>$data->value('service'),
            'service_id'=>$data->value('service_id'),
            'pet_id'=>$data->value('pet_id'),
            'type'=>$data->value('type'),
            'status'=>$data->value('status'),
            'total'=>$data->value('total'),
            'diskon'=>$data->value('diskon'),
            'coupon_name'=>$data->value('coupon_name'),
            'subtotal'=>$data->value('subtotal'),
            'payment_id'=>$data->value('payment_id'),
            'allowed_payment'=>$payment_allowed,
            'payment_method'=>$data->value('payment_method'),
            'booking_date'=>$data->value('booking_date'),
            'payed_at'=>$data->value('payed_at'),
            'payed_untill'=>$data->value('payed_untill'),
            'payment_url'=>$data->value('payment_url'),
            'cancelled_at'=>$data->value('cancelled_at'),
            'cancelled_reason'=>$data->value('cancelled_reason'),
            'users_ids'=>$data->value('users_ids'),
            'partner_user_id' => $data->value('partner_user_id'),
            'partner_detail' => $res,
            'comission' => $data->value('comission'),
            'partner_paid_status' => $data->value('partner_paid_status'),
            'partner_paid_ammount' => $data->value('partner_paid_ammount'),
            'partner_paid_at' => $data->value('partner_paid_at'),
            'refund_at' => $data->value('refund_at'),
            'created_at'=>$data->value('created_at'),
            'updated_at'=>$data->value('updated_at')
        ]; 

        return response()->json([
            'status'=>'success',
            'results'=>$arr
        ]);
       
        
    }

    public function create_payment(request $request){
        $orderId = $request->id;
        $payment_method = $request->payment_method;
        $payment_method_id = $request->payment_method_id;
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $wallet = wallet::where('users_ids', $result['body']['user_id'])->where('type','pawly_credit');
        $ammount = $wallet->sum('debit') - $wallet->sum('credit');
        $total_transaction = orderservice::where('order_id','like', $orderId)->value('subtotal');
        
        $data = orderservice::where('order_id','like',$orderId);
        $user = User::where('id','like', $result['body']['user_id']);
        if($result['status'] == 200){
            if($payment_method == 'Wallet'){
                if($total_transaction < $ammount){
                    $current_date_time = date('Y-m-d H:i:s');
                $query = wallet::insertGetId([
                    'users_ids' => $result['body']['user_id'], 
                    'debit' => '',
                    'credit' => $data->value('subtotal'),
                    'description' => 'Payment order ID '.$orderId,
                    'type' => 'pawly_credit',
                    'created_at' => $current_date_time
                ]);
                $wallet = wallet::where('id',$query)->get();
                if($wallet->count()==1){
                    $statuschange = orderservice::where('order_id',$orderId)->update([
                        'status' => 'BOOKING RESERVED',
                        'payment_method' => 'Wallet',
                        'payment_id' => $query,
                        'payed_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    $token_fb = $this->fb_token->userFirebaseToken( orderservice::where('order_id','like',$orderId)->value('users_ids'),'Consumer App');
                    foreach( $token_fb as $token){
                    if($token['firebase_token'] != NULL){
                        $notification = $this->mobile_banner->send_notif('Your payment has been received','Thank you for payment order '.$orderId,'','',$token['firebase_token']);
                    }
                    $this->prosesOrder($orderId);
                }
                }
                return response()->JSON([
                    'status' => 'success',
                    'payment_url' => 'https://web.pawly.my.id/',
                    'success_url' => ''
                ]);
                } else{
                    return response()->JSON([
                        'status' => 'error',
                        'msg' => 'Sorry, your balance is less than the total transaction'
                    ]);
                }
                
            }else{
                $url = env('MOOTA_URL');
                //$timestamp = Carbon::now()->timestamp;
                $data = array(
                        'invoice_number' => $orderId,
                        'amount' => $data->value('subtotal'),
                        'payment_method_id' => $payment_method_id,
                        'type' => 'payment',
                        'callback_url' => env('Activate_Account_URL').'/api/order/changestatus',
                        'expired_date' => date('Y-m-d H:i:s', $data->value('payed_untill')),
                        'description' => 'pawly-order',
                        'increase_total_from_unique_code' => 1,
                        'customer' => [
                            'name'=> $user->value('nickname'),
                            'email'=>  $user->value('email'),
                            'phone'=>  $user->value('phone_number')
                        ],
                        'items'=>[
                            [
                                'name'=>$data->value('type'),
                                'qty'=>1,
                                'price'=>intval($data->value('subtotal')),
                                'sku'=>$data->value('service_id'), 
                                'image_url'=>''
                            ]
                        ],
                        'with_unique_code' => 1,
                        'start_unique_code' => 500,
                        'end_unique_code' =>999,
                        'unique_code' => 0                       
                 );
        
                $response = Http::withHeaders([
                    'Location' => '/api/v2/contract',
                    'Authorization' => env('payment_bearer'),
                    'Accept' => 'application/json'
                ])->post($url, $data);
                $saveddata = $response->json();
                if(array_key_exists("success", $saveddata)){
                    $updatelink = orderservice::where('order_id',$orderId)->update([
                        'payment_method' => $payment_method,
                        'payment_url' => $saveddata['data']['payment_link'],
                        'updated_at' => Carbon::now()
                    ]);
                    return response()->JSON([
                        'status' => 'success',
                        'payment_url' => $saveddata['data']['payment_link'],
                        'success_url' => ''
                    ]);
                } else {
                    return response()->JSON([
                        'status' => 'error',
                        'msg' => $saveddata,
                        'payment_url' => '',
                        'success_url' => ''
                    ]);
                }
               
                //return $data;
            }
        }else{
            return $result;
        }
        
    }

    public function changestatus(request $request){

        $status = $request->status;
        $trx_id = $request->trx_id;
        $payment_at = $request->payment_at;
        $invoice = $request->invoice_number;

        if($status=='success'){
            $query = orderservice::where('order_id','like',$invoice)->where('status','like','PENDING_PAYMENT')->update([
                'status' => 'BOOKING RESERVED',
                'payed_at' => $payment_at,
                'payment_id' => $trx_id,
                'updated_at' => Carbon::now()
            ]);
            //Jika success
            if($query==1){
                $token_fb = $this->fb_token->userFirebaseToken( orderservice::where('order_id','like',$invoice)->value('users_ids'),'Consumer App');
                foreach( $token_fb as $token){
                    if($token['firebase_token'] != NULL){
                        $notification = $this->mobile_banner->send_notif('Your payment has been received','Thank you for payment order '.$invoice,'','',$token['firebase_token']);
                    }
                }
                $this->prosesOrder($invoice);

                return response()->JSON([
                    'status' => 'success',
                ]);
            } else{
                return response()->JSON([
                    'status' => 'error',
                    'msg' => ''
                ]);
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => ''
            ]);
        }
        
    }

    public function prosesOrder($order_id){
        $query = orderservice::where('order_id','like',$order_id);

        if($query->value('type')== 'doctor'){
            if($query->value('service')== 'vidcall'){
                //$this->createVcLink();
            }
        }else if($query->value('type')== 'wallet'){
            $this->wallet->AddAmmount($query->value('users_ids'),$query->value('total'),null,'pawly_credit','Top Up Saldo '.$order_id);
        }
    }

       
    public function createVcLink(request $request){
        $query = orderservice::where('order_id','like',$request->order_id);
        $url = env('Whereby_URL');
        $newDateTime = Carbon::now()->addMinute(20)->toISOString();
        //$timestamp = Carbon::now()->timestamp;
        $data = array(
                'isLocked' => false,
                'roomNamePrefix' =>  $request->order_id,
                'roomNamePattern' => 'uuid',
                'roomMode' => 'normal',
                'endDate' => $newDateTime,
                'recording' => [
                    'type'=> 'none',
                    'destination' => [
                        'provider'=> 's3',
                        'bucket'=> 'string',
                        'provider'=> 's3',
                        'accessKeyId' =>  "string",
                        'accessKeySecret' =>  "string",
                        'fileFormat' =>  "mkv"
                    ],
                    'startTrigger'=> 'none',
                ],
                'fields' => [
                    "hostRoomUrl"
                ]                   
         );

        $response = Http::withHeaders([
            'Authorization' => env('Whereby_Token'),
            'Accept' => 'application/json'
        ])->post($url, $data);
        $saveddata = $response->json();
        $query = vidcalldetail::insert([
            'booking_id' =>  $request->order_id, 
            'link_partner' =>  $saveddata['hostRoomUrl'] ,
            'link_user' => $saveddata['roomUrl'],
            'session_done_until' => strtotime($saveddata['endDate']),
            'meeting_id' => $saveddata['meetingId'],
            'status' => 'Active',
            'created_at' => Carbon::now()
        ]);
        if($query == 1){
            return response()->JSON([
                'status' => 'success',
                'msg' => ''
            ]);
        }else{
            return response()->JSON([
                'status' => 'error',
                'msg' => ''
            ]);
        }
        
    }

    public function saasApointment(request $request){
        $orderId = $request->orderId;
        $type = $request->type;
        $service = $request->service;
        $status = $request->status;
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

        if($request->date== 'Tomorrow'){
            $date = Carbon::tomorrow()->toDateString();
        }else if($request->date== 'Today'){
            $date = Carbon::today()->toDateString();
        }else{
            $date = '';
        }
            
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
    
        if($result['status'] == 200){ 
            $data = orderservice::where('partner_user_id','like', $result['body']['user_id'])->where('order_id','like','%'.$orderId.'%')->where('type','like','%'.$type.'%')->where('service','like','%'.$service.'%')->where('status','like','%'.$status.'%')->where('booking_date','like','%'.$date.'%')->orderBy('booking_date','ASC');
            $result=[];
                
            foreach($data->limit($limit)->offset($page)->get() as $arr){
                if($arr['coupon_name']==NULL){
                    $payment_allowed = 'a:2:{i:0;s:4:"dana";i:1;s:3:"ovo";}';
                } else{
                    $payment_allowed = couponservice::where('coupon_name',$data->value('coupon_name'))->value('allowed_payment');
                }
                $method = array(
                    'id' => $arr['id'],
                    'order_id'=>$arr['order_id'],
                    'service'=>$arr['service'],
                    'service_id'=>$arr['service_id'],
                    'pet_id'=>$arr['pet_id'],
                    'type'=>$arr['type'],
                    'status'=>$arr['status'],
                    'total'=>$arr['total'],
                     'diskon'=>$arr['diskon'],
                    'coupon_name'=>$arr['coupon_name'],
                    'subtotal'=>$arr['subtotal'],
                    'allowed_payment'=>$payment_allowed,
                    'payment_method'=>$arr['payment_method'],
                    'payment_id'=>$arr['payment_id'],
                    'booking_date'=>$arr['booking_date'],
                    'payed_at'=>$arr['payed_at'],
                    'payed_untill'=>$arr['payed_untill'],
                    'cancelled_at'=>$arr['cancelled_at'],
                    'cancelled_reason'=>$arr['cancelled_reason'],
                    'users_ids'=>$arr['users_ids'],
                    'user_name'=>User::where('id',$arr['users_ids'])->value('nickname'),
                    'partner_user_id'=>$arr['partner_user_id'],
                    'comission'=>$arr['comission'],
                    'partner_paid_status'=>$arr['partner_paid_status'],
                    'partner_paid_ammount'=>$arr['partner_paid_ammount'],
                    'partner_paid_at'=>$arr['partner_paid_at'],
                    'refund_at'=>$arr['refund_at'],
                    'created_at'=>$arr['created_at'],
                    'updated_at'=>$arr['updated_at']
                );
                array_push($result, $method);
            }
            return response()->json([
                'status'=>'success',  
                'total_data'=>$data->count(),  
                'total_page'=> ceil($data->count() / $limit),
                'results'=>$result
            ]);
        }else{
            return $result;
        }     
    }

    public function saasNewOrder(request $request){
        $orderId = $request->orderId;
        $type = $request->type;
        $service = $request->service;
        $status = $request->status;
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

        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
    
        if($result['status'] == 200){ 
            $data = orderservice::where('partner_user_id','like', $result['body']['user_id'])->where('order_id','like','%'.$orderId.'%')->where('type','like','%'.$type.'%')->where('service','like','%'.$service.'%')->where('status','like','%'.$status.'%')->orderBy('booking_date','ASC');
            $result=[];
                
            foreach($data->limit($limit)->offset($page)->get() as $arr){
                if($arr['coupon_name']==NULL){
                    $payment_allowed = 'a:2:{i:0;s:4:"dana";i:1;s:3:"ovo";}';
                } else{
                    $payment_allowed = couponservice::where('coupon_name',$data->value('coupon_name'))->value('allowed_payment');
                }
                $method = array(
                    'id' => $arr['id'],
                    'order_id'=>$arr['order_id'],
                    'service'=>$arr['service'],
                    'service_id'=>$arr['service_id'],
                    'pet_id'=>$arr['pet_id'],
                    'type'=>$arr['type'],
                    'status'=>$arr['status'],
                    'total'=>$arr['total'],
                     'diskon'=>$arr['diskon'],
                    'coupon_name'=>$arr['coupon_name'],
                    'subtotal'=>$arr['subtotal'],
                    'allowed_payment'=>$payment_allowed,
                    'payment_method'=>$arr['payment_method'],
                    'payment_id'=>$arr['payment_id'],
                    'booking_date'=>$arr['booking_date'],
                    'payed_at'=>$arr['payed_at'],
                    'payed_untill'=>$arr['payed_untill'],
                    'cancelled_at'=>$arr['cancelled_at'],
                    'cancelled_reason'=>$arr['cancelled_reason'],
                    'users_ids'=>$arr['users_ids'],
                    'user_name'=>User::where('id',$arr['users_ids'])->value('nickname'),
                    'partner_user_id'=>$arr['partner_user_id'],
                    'comission'=>$arr['comission'],
                    'partner_paid_status'=>$arr['partner_paid_status'],
                    'partner_paid_ammount'=>$arr['partner_paid_ammount'],
                    'partner_paid_at'=>$arr['partner_paid_at'],
                    'refund_at'=>$arr['refund_at'],
                    'created_at'=>$arr['created_at'],
                    'updated_at'=>$arr['updated_at']
                );
                array_push($result, $method);
            }
            return response()->json([
                'status'=>'success',  
                'total_data'=>$data->count(),  
                'total_page'=> ceil($data->count() / $limit),
                'results'=>$result
            ]);
        }else{
            return $result;
        }     
    }

    public function rejectorder(request $request){
        $order_id = $request->orderid;

        $order = orderservice::where('order_id',$order_id)->get();

        if($order->value('status')=='BOOKING RESERVED'){
            $update = orderservice::where('order_id',$order_id)->update([
                'cancelled_at' => carbon::now()->timestamp,
                'cancelled_reason' => 'doctor reject order',
                'status' => 'BOOKING_CANCEL',
                'refunded_at' => carbon::now(),

            ]);

            $token_fb = $this->fb_token->userFirebaseToken( orderservice::where('order_id','like',$order_id)->value('users_ids'),'Consumer App');
                    foreach( $token_fb as $token){
                    if($token['firebase_token'] != NULL){
                        $notification = $this->mobile_banner->send_notif('Your Payment Has Been Cancelled','Your Money will be refuned 1x24'.$order_id,'','',$token['firebase_token']);
                    }

            $refund = wallet::insert([
                'users_ids' => $order->value('users_ids'),
                'debit' => $order->value('subtotal'),
                'type' => 'pawly_credit',
                'created_at' => carbon::now(),
                'description' => 'Refund for order ID'.$order_id
            ]);

            if($refund==1){
                return response()->JSON([
                    'status' => 'success',
                    'msg' => 'refund_success'
                ]);
             }
            }
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'You can not cancel this order'
            ]);
            
        }
    }
}