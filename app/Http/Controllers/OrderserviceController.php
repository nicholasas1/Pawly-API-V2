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
use Carbon\Carbon;
use App\Models\doctor;
use App\Models\clinic;
use App\Models\wallet;
use Illuminate\Support\Facades\Http;
use Symfony\Component\VarDumper\VarDumper;
use App\Models\User;

class OrderserviceController extends Controller
{
    protected $coupons;
    protected $JWTValidator;
    public function __construct(CouponserviceController $coupons, JWTValidator $jWTValidator)
    {
        $this->coupons = $coupons;
        $this->JWTValidator = $jWTValidator;
    }

    public function order_service(request $request){

        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);

        if($result['status'] == 200){
            $service = $request->service;
            $price = $request->price;
            $coupon_name = $request->coupon;
            $service_id = $request->servid;
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
                $ordercode = 'CHOL';
                $paid_until = time()+ 3600;
                $comission = 5000;
            } else if($service=='vidcall'){
                $ordercode = 'VCOL';
                $paid_until = time()+ 3600;
                $comission = 5000;
            } else if($service=='onsite'){
                $ordercode = 'OSM';
                $dateformat = date($booking_time);
                $paid_until = strtotime($dateformat) - 3600*2;
                $comission = $price * 12/100;
            } else{
                $paid_until = time()+ 3600*24;
                $comission = $price * 12/100;
            }

        
            if($coupon_name==NULL){
                $total_price = $price;
                $discount = 0;
                $subtotal = $total_price-$discount;
                $query = orderservice::insertGetId([
                    'service' => $service,
                    'service_id' => $service_id,
                    'type' => $type,
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
                    'order_id' => $ordercode.substr(str_shuffle(str_repeat($pool, 5)), 0, 8).$query
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
            $data = orderservice::where('users_ids','like', $result['body']['user_id'])->where('order_id','like','%'.$orderId.'%')->where('type','like','%'.$type.'%')->where('service','like','%'.$service.'%')->where('status','like','%'.$status.'%');
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
        $wallet = wallet::where('users_ids', $result['body']['user_id']);
        $ammount = $wallet->sum('debit') - $wallet->sum('credit');
        $total_transaction = orderservice::where('order_id','like', $orderId)->value('subtotal');
        
        $data = orderservice::where('order_id','like',$orderId);
        $user = User::where('id','like', $result['body']['user_id']);
        if($result['status'] == 200){
            if($payment_method == 'Wallet'){
                if($total_transaction > $ammount){
                    $current_date_time = date('Y-m-d H:i:s');
                $query = wallet::insertGetId([
                    'users_ids' => $result['body']['user_id'], 
                    'debit' => '',
                    'credit' => $data->value('subtotal'),
                    'created_at' => $current_date_time
                ]);
                $wallet = wallet::where('id',$query)->get();
                if($wallet->count()==1){
                    $statuschange = orderservice::where('order_id',$orderId)->update([
                        'status' => 'BOOKING RESERVE',
                        'payment_method' => 'Wallet',
                        'payment_id' => $query,
                        'payed_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
                return response()->JSON([
                    'status' => 'success',
                    'payment_url' => '',
                    'success_url' => ''
                ]);
                } else{
                    return response()->JSON([
                        'status' => 'error',
                        'msg' => 'Sorry, your balance is less than the total transaction'
                    ]);
                }
                
            }else{
                $url = 'https://private-anon-8fe5e9742d-mootaapiv2.apiary-proxy.com/api/v2/contract';
                //$timestamp = Carbon::now()->timestamp;
                $data = array(
                        'invoice_number' => $orderId,
                        'amount' => $data->value('subtotal'),
                        'payment_method_id' => $payment_method_id,
                        'type' => 'payment',
                        'callback_url' => 'https://app.moota.co/debug/webhook',
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
                                'price'=>$data->value('subtotal'),
                                'sku'=>$data->value('service_id'), 
                                'image_url'=>''
                            ]
                        ],
                        'with_unique_code' => 1,
                        'start_unique_code' => 10,
                        'end_unique_code' =>999,
                        'unique_code' => 0                       
                 );
        
                $response = Http::withHeaders([
                    'Location' => '/api/v2/contract',
                    'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJucWllNHN3OGxsdyIsImp0aSI6ImQ3ODEyNDk1ZjI4YTU2OGIwMTcyNGU5MmEwZGI1NGFlYzE3NTE2YmE5YmUyMWZkOWYzZjY5OGZlMDNmMjcxZmU5NmU3OGZhM2U5MjdmMWU4IiwiaWF0IjoxNjY1NjU0MDIxLjk4OTk5MiwibmJmIjoxNjY1NjU0MDIxLjk4OTk5NSwiZXhwIjoxNjk3MTkwMDIxLjk4NzAyMywic3ViIjoiMjIzNjkiLCJzY29wZXMiOlsiYXBpIl19.pfLbznUT_n-hcSBCMu7LIpdzQwrPlXPuTUaYExYL5-spmMksjg77b_6_rdtGA3sgqH_HFxTENxbwBTJ5KKCPM_8J5k2bQa4TN2zVPeCOBwDGyh8Pn_eTxWXlcM-U0xnrz2kleDExty0iQaQkQ97TyuSSHo-2m6fpMREFqSPnrjF_bBWLwow5EDMYRqeugwWL2c2hXuiR5MlWePwS79vrYsydEt36n5PyQzMDv6AjK9L4O8DC54fLPtgE4POc7pcdJiViy5ktg9Y2xHGjUv7T4okbyA1dn2F_lePQnqP228RkerAEx0TqL12FnTPytMEqQGpPM1JObwduqIV7IHRXNqH71M6ONUL4qPvFhAdmTodTevgXcmVqbOp51qAnH25gayy8W_9Qe73Qq3dIkvy5vglsAHJGEl23zA6wOJ2APSLhiQDDf3mHsKBTNzocYUDByENc1HbEsa3zw0Sh05PwFYN4PoJ60MGFhzqx0c5Z-m0jZ3_PhziQefyKt_g8fYJC3L8VQuBGA6EFDkF2oberWQ7_4quqyXO-mQ7ufCsa5ji_54MuL1CPfz-Iz-xdeGOT1Lh2zn8fzZ22k3b_gF409hxSynryfGvMT8UQb_2fjnUApWF88ODKDon4qAUP28LzBNKsjo3jr0UAAzJy8aenmNfOrVBiTXN3GwHFs2osBWg',
                    'Accept' => 'application/json'
                ])->post($url, $data);
        
                return $response->json();
                //return $data;
            }
        }else{
            return $result;
        }
        
    }
        
}
