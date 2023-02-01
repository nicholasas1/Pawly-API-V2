<?php

namespace App\Http\Controllers;

use App\Models\orderservice;
use App\Models\couponusages;
use App\Models\couponservice;
use App\Models\order_detail;
use Illuminate\Http\Request;
use App\Http\Controllers\CouponserviceController;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;
use App\Models\notificationdb;
use App\Http\Controllers\NotificationdbController;
use App\Http\Controllers\JWTValidator;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\MailServer;
use App\Http\Controllers\ClinicController;
use Carbon\Carbon;
use App\Models\doctor;
use App\Models\clinic;
use App\Models\wallet;
use App\Models\Medicine;
use App\Models\Penanganan;
use App\Models\vidcalldetail;
use Illuminate\Support\Facades\Http;
use Symfony\Component\VarDumper\VarDumper;
use App\Models\User;
use App\Http\Controllers\FirebaseTokenController;
use App\Http\Controllers\MobileBannerController;
use App\Models\ratings;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\whatsapp_notif;
use App\Models\RekamMedis;
use App\Http\Controllers\socket_notf;

class OrderserviceController extends Controller
{
    protected $coupons;
    protected $JWTValidator;
    protected $notif;
    public function __construct(ClinicController $clinics,socket_notf $socket, NotificationdbController $notif,whatsapp_notif $whatsapp,MailServer $mailServer,WalletController $wallet,CouponserviceController $coupons, JWTValidator $jWTValidator,FirebaseTokenController $fb_token,MobileBannerController $mobile_banner)
    {
        $this->coupons = $coupons;
        $this->notif = $notif;
        $this->clinics = $clinics;
        $this->JWTValidator = $jWTValidator;
        $this->fb_token = $fb_token;
        $this->mobile_banner = $mobile_banner;
        $this->wallet = $wallet;
        $this->mailServer = $mailServer;
        $this->whatsapp = $whatsapp;
        $this->socket = $socket;

    }

    public function order_service(request $request){

        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);

        if($result['status'] == 200){
            $service = $request->service;
            $price = $request->price;
            $coupon_name = $request->coupon;
            $service_id = $request->servid;
            $clinic_id = $request->clinic_id;
            $pet_id = $request->pet_id;
            $type = $request->type;
            $partner_user_id = $request->partner_user_id;
            $booking_time = $request->booking_time;
            $booking_date = $request->booking_date;
            
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
                $ordercode = 'A';
                $paid_until = time()+ 3600*24;
            }

            $res=[];
            $user=[];
            $user_detail = User::where('id','like', $userid);
            $user = [
                    'nickname' => $user_detail->value('nickname'),
                    'profile_picture'=>$user_detail->value('profile_picture'),
                    'email'=>$user_detail->value('email'),
                    'phone_number'=>$user_detail->value('phone_number')
            ];
            if($type == 'doctor'){
                $detail = doctor::where('id','like', $service_id);
                $res = [
                    'account_id' => $detail->value('users_ids'),
                    'id'=>$detail->value('id'),
                    'name'=>$detail->value('doctor_name'),
                    'phone_number'=>User::where('id','like',$detail->value('users_ids'))->value('phone_number'),
                    'profile_picture'=>$detail->value('profile_picture')
                ];
            }else if($type == 'clinic'){
                $detail = clinic::where('id','like', $service_id);
                $checkschedule = $this->clinics->checkschedule($booking_date,$booking_time,$detail->value('id'));
                if($checkschedule == 'NOT AVAIABLE' ){
                    return response()->JSON([
                        'status' => 'error',
                        'msg' => 'Date and Time already Booked'
                    ]);
                } else{
                    $res = [
                        'account_id' => $detail->value('user_id'),
                        'id'=>$detail->value('id'),
                        'name'=>$detail->value('clinic_name'),
                        'phone_number'=>User::where('id','like',$detail->value('user_id'))->value('phone_number'),
                        'profile_picture'=>$detail->value('clinic_photo')
                    ];
                }
            }else{
                $res = [
                    'account_id' => '',
                    'id'=>'',
                    'name'=>'',
                    'phone_number'=>'6288213276665',
                    'profile_picture'=>''
                ];
            }

            if($type=='wallet'){
                $partner_user_id=0;
            }
        
            if($coupon_name==NULL){
                $total_price = 0;
                $discount = 0;
                $query = orderservice::insertGetId([
                    'type' => $type,
                    'pet_id' => $pet_id,
                    'service_id' => $service_id,
                    'clinic_id' => $clinic_id,
                    'users_ids' => $userid,
                    'status' => 'PENDING_PAYMENT',
                    'created_at' => Carbon::now(),
                    'partner_user_id' => $partner_user_id,
                    'comission' => $comission,
                    'payed_untill' => $paid_until,
                    'booking_date' => $booking_date,
                    'booking_time' => $booking_time
                ]);

                $serv=[];
                $orderId = $ordercode.substr(str_shuffle(str_repeat($pool, 5)), 0, 3).$query;
                foreach($service as $orderlist){
                    $orderinsert = order_detail::insert([
                        'order_id' => $orderId,
                        'service_id' => $orderlist['service_id'],
                        'service_name' => $orderlist['service_name'],
                        'order_price' => $orderlist['order_price']
                    ]);
                    $temps = [
                        'service_name'=> $orderlist['service_name'],
                        'service_name' => $orderlist['order_price']
                      ];
                      array_push($serv,$temps);
                    $total_price = $total_price+$orderlist['order_price'];
                }
                $discount = 0;
                $subtotal = $total_price-$discount;
                $insertorderid = orderservice::where('id',$query)->update([
                    'order_Id' => $orderId,
                    'total' => $total_price,
                    'subtotal' => $subtotal,
                    'diskon' => $discount
                ]);
                $details = [
                    'user_detail' =>$user,
                    'order_id' =>$orderId,
                    'service' => order_detail::where('order_id','like',$orderId)->select('service_name')->get(),
                    'type' => $type,
                    'booking_date' => $booking_time,
                    'total_price' => $total_price,
                    'total_payment' => $subtotal,
                    'partnerDetail' => $res
                ];
                if($insertorderid==1){
                    $this->mailServer->InvoicePendingPayment($details);
                    $this->notif->createnotif($partner_user_id,$type,$partner_user_id,$orderId,'New Order '.$orderId.' from '.$user_detail->value('nickname'),NULL); //notif partner
                    $this->notif->createnotif($userid,'user',$partner_user_id,$orderId,'New Order '.$orderId.' from '.$user_detail->value('nickname'),NULL); //notif user
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
                $coupons_respond = $this->coupons->coupon_service($coupon_name,$userid,$service);
              
                if($coupons_respond['status']=='success'){
                $total_price = 0;
                $discount = 0;
                $query = orderservice::insertGetId([
                    'type' => $type,
                    'pet_id' => $pet_id,
                    'service_id' => $service_id,
                    'clinic_id' => $clinic_id,
                    'users_ids' => $userid,
                    'status' => 'PENDING_PAYMENT',
                    'created_at' => Carbon::now(),
                    'partner_user_id' => $partner_user_id,
                    'comission' => $comission,
                    'payed_untill' => $paid_until,
                    'booking_date' => $booking_date,
                    'booking_time' => $booking_time
                ]);

                $serv=[];
                $orderId = $ordercode.substr(str_shuffle(str_repeat($pool, 5)), 0, 3).$query;
                foreach($service as $orderlist){
                    $orderinsert = order_detail::insert([
                        'order_id' => $orderId,
                        'service_id' => $orderlist['service_id'],
                        'service_name' => $orderlist['service_name'],
                        'order_price' => $orderlist['order_price']
                    ]);
                    $temps = [
                        'service_name'=> $orderlist['service_name'],
                        'service_name' => $orderlist['order_price']
                      ];
                      array_push($serv,$temps);
                    $total_price = $total_price+$orderlist['order_price'];
                }
                $discount = 0;
                $subtotal = $total_price-$discount;
                $insertorderid = orderservice::where('id',$query)->update([
                    'order_Id' => $orderId,
                    'total' => $total_price,
                    'subtotal' => $subtotal,
                    'diskon' => $discount
                ]);
                $details = [
                    'user_detail' =>$user,
                    'order_id' =>$orderId,
                    'service' => order_detail::where('order_id','like',$orderId)->select('service_name')->get(),
                    'type' => $type,
                    'booking_date' => $booking_time,
                    'total_price' => $total_price,
                    'total_payment' => $subtotal,
                    'partnerDetail' => $res
                ];

                if($insertorderid==1){
                    $this->mailServer->InvoicePendingPayment($details);
                    $this->notif->createnotif($userid,$type,$partner_user_id,$orderId,'New Order '.$orderId.' from '.$user_detail->value('nickname'),NULL);
                    $this->notif->createnotif($userid,'user',$partner_user_id,$orderId,'New Order '.$orderId.' from '.$user_detail->value('nickname'),NULL); //notif user
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
        $partner_id = $request->partner_id;
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
        
        if($service==NULL){
            $service = ['umum','vaksin','grooming'];
        }

        $data = orderservice::where('order_id','like','%'.$orderId.'%')
        ->join('order_details','orderservices.order_id','=','order_details.order_id')
        ->where('type','like','%'.$type.'%')
        ->wherein('order_details.service_name',$service)
        ->where('status','like','%'.$status.'%')
        ->where('partner_user_id','like','%'.$partner_id.'%');
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
                'service'=>order_detail::where('order_id','like',$orderId)->select('service_name')->get(),
                'service_id'=>$arr['service_id'],
                'clinic_id'=>$arr['clinic_id'],
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

        if($service == NULL){
            $service = ['grooming','umum','vaksin'];
        }

        if($result['status'] == 200){
            // $data = orderservice::where('order_id','like','%'.$orderId.'%')
            // ->join('order_details','orderservices.order_id','=','order_details.order_id')
            // ->where('type','like','%'.$type.'%')
            // ->wherein('order_details.service_name',$service)
            // ->where('status','like','%'.$status.'%')
            // ->where('partner_user_id','like','%'.$partner_id.'%');

            $data = orderservice::where('users_ids','like', $result['body']['user_id'])
            ->join('order_details','orderservices.order_id','=','order_details.order_id')
            ->where('orderservices.order_id','like','%'.$orderId.'%')
            ->where('type','like','%'.$type.'%')
            ->wherein('order_details.service_name',$service)
            ->where('status','like','%'.$status.'%')
            ->orderBy('created_at','DESC');

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
                    'service'=>order_detail::where('order_id','like',$arr['order_id'])->select('service_name')->get(),
                    'service_id'=>$arr['service_id'],
                    'clinic_id'=>$arr['clinic_id'],
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

    public function orderListPartner(Request $request){
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
        
        if($service==NULL){
            $service = ['umum','vaksin','grooming'];
        }
        
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);

        if($result['status'] == 200){
            $data2 = orderservice::where('partner_user_id','like', $result['body']['user_id'])
            ->join('order_details','orderservices.order_id','=','order_details.order_id')
            ->where('orderservices.order_id','like','%'.$orderId.'%')
            ->where('type','like','%'.$type.'%')
            ->wherein('order_details.service_name',$service)
            ->where('status','like','%'.$status.'%')
            ->orderBy('created_at','DESC');

            $data = orderservice::where('partner_user_id','like', $result['body']['user_id'])
            ->join('order_details','orderservices.order_id','=','order_details.order_id')
            ->where('orderservices.order_id','like','%'.$orderId.'%')
            ->where('type','like','%'.$type.'%')
            ->wherein('order_details.service_name',$service)
            ->where('status','like','%'.$status.'%')
            ->orderBy('created_at','DESC');
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
                    'service'=>order_detail::where('order_id','like',$orderId)->select('service_name')->get(),
                    'service_id'=>$arr['service_id'],
                    'clinic_id'=>$arr['clinic_id'],
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
                'total_data'=>$data2->count(),  
                'total_page'=> ceil($data2->count() / $limit),
                'results'=>$result
            ]);
        }else{
            return $result;
        }     
    }

    public function getDetail(request $request){
        return response()->json($this->orderDetail($request->id));
    }

    public function orderDetail($orderId){
        $vcDetail=[];
        $res=[];
        //Partner Detail
        $data = orderservice::where('order_id','like',$orderId);
        if($data->value('type') == 'doctor'){
            $detail = doctor::where('id','like',$data->value('service_id'));
            $res = [
                'account_id' => $detail->value('users_ids'),
                'id'=>$detail->value('id'),
                'name'=>$detail->value('doctor_name'),
                'profile_picture'=>$detail->value('profile_picture'),
                'phone_number'=>User::where('id','like', $detail->value('users_ids'))->value('phone_number'),
                'address' => $detail->value('address')
            ];
        }else  if($data->value('type') == 'wallet'){
            $res = [
                'account_id' => '',
                'id'=>'',
                'name'=>'Pawly Admin',
                'profile_picture'=>'',
                'phone_number'=> '6288213276665',
                'address' => ''
            ];
        }

        if(order_detail::where('order_id','like','%'.$orderId.'%')->select('service_name')->get() == 'vidcall'){
            $detail = vidcalldetail::where('booking_id','like',$data->value('order_id'));
            $vcDetail = [
                'status'=>$detail->value('status'),
                'link_partner' => $detail->value('link_partner'),
                'link_user'=>$detail->value('link_user'),
                'meeting_id'=>$detail->value('meeting_id'),
                'session_done_time'=>$detail->value('session_done_time'),
                'partner_join_time'=>$detail->value('partner_join_time'),
                'user_join_time'=>$detail->value('user_join_time'),
            ];
        }
        $rating = ratings::where('booking_id','=',$orderId);

        if($rating->count() == 1 ){
            $is_rating = true;
        }else{
            $is_rating = false;
        }

        $pawlycheck = order_detail::where('order_id','like',$orderId)->where('service_name','not like','pawly_credit')->get();

        if($data->value('status') == 'ORDER_COMPLATE' && $is_rating == false &&  $pawlycheck->count()!=0){
            $can_rating = true;
        }else{
            $can_rating = false;
        }

        if($data->value('coupon_name')==NULL){
            $payment_allowed = '';
        } else{
            $payment_allowed =  couponservice::where('coupon_name',$data->value('coupon_name'))->value('allowed_payment');
        }

        $rekammedis = [];
        $obat = [];
        $penanganan = [];
        
        if(RekamMedis::where('order_id',$data->value('order_id'))->get()->count() > 0){
            $rekammedis = RekamMedis::where('order_id',$data->value('order_id'))->select('id','keluhan','penanganan_sementara','penanganan_lanjut','diagnosa')->get(); 
            if(medicine::where('rm_id',$rekammedis->value('id'))->get()->count()>0){
                $obat = medicine::where('rm_id',$rekammedis->value('id'))->get();
            }
            if(penanganan::where('rm_ids',$rekammedis->value('id'))->get()->count()>0){
                $penanganan = penanganan::where('rm_ids',$rekammedis->value('id'))->get();
            }          
            $rekammedis = ['keluhan' => $rekammedis->value('keluhan'),
            'penanganan_sementara'=>$rekammedis->value('penanganan_sementara'),
            'penanganan_lanjut'=>$rekammedis->value('penanganan_lanjut'),
            'diagnosa'=>$rekammedis->value('diagnosa')];
        }
        $user_detail = User::where('id','like', $data->value('users_ids'));

        $arr = [
            'id' => $data->value('id'),
            'order_id'=>$data->value('order_id'),
            'type'=>$data->value('type'),
            'service'=>order_detail::where('order_id','like',$data->value('order_id'))->select('service_name')->get(),
            'service_id'=>$data->value('service_id'),
            'video_call_detail'=>$vcDetail,
            'pet_id'=>$data->value('pet_id'),
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
            'is_rating'=> $is_rating,
            'can_rating'=> $can_rating,
            'cancelled_reason'=>$data->value('cancelled_reason'),
            'users_ids'=>$data->value('users_ids'),
            'user_detail' => [
                'nickname' => $user_detail->value('nickname'),
                'profile_picture'=>$user_detail->value('profile_picture'),
                'email'=>$user_detail->value('email'),
                'phone_number'=>$user_detail->value('phone_number')
            ],
            'partner_user_id' => $data->value('partner_user_id'),
            'partner_detail' => $res,
            'comission' => $data->value('comission'),
            'partner_paid_status' => $data->value('partner_paid_status'),
            'partner_paid_ammount' => $data->value('partner_paid_ammount'),
            'partner_paid_at' => $data->value('partner_paid_at'),
            'refund_at' => $data->value('refund_at'),
            'rekam_medis'=>$rekammedis,
            'medicine' => $obat,
            'penanganan' => $penanganan,
            'created_at'=>$data->value('created_at'),
            'updated_at'=>$data->value('updated_at')
        ]; 

        return [
            'status'=>'success',
            'results'=>$arr
        ];
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
                                $notification = $this->mobile_banner->send_notif('Your payment has been received','Thank you for payment order '.$orderId,'','',$token['firebase_token'],'/tabs/tab3',NULL);
                            }
                        }
                        $orderDetail = $this->orderDetail($orderId);
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
                        $this->mailServer->InvoicePaymentSuccessCusttomer($details);
                        $chat = "Hallo, ".$details['partnerDetail']['name']." , mau info Ada bookingan masuk dari PAWLY APP:\n\nNama : ".$details['user_detail']['nickname']."\nBooking Service : ".$details['type']." - ".$details['service']."\nBooking Code : ".$details['order_id']."\n\nMohon dibantu proses ya kak, Terimakasih 🙏😊";

                        $wa = $this->whatsapp->sendWaText($details['partnerDetail']['phone_number'], $chat);
                        $this->socket->update_order($orderId, $orderDetail['results']['status'],$orderDetail['results']['users_ids'],$orderDetail['results']['partner_user_id']);
               
                        $this->prosesOrder($orderId);
                    }
                    return response()->JSON([
                        'status' => 'success',
                        'payment_url' => env('Activate_Account_URL').'/thankYouPage',
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
                        $notification = $this->mobile_banner->send_notif('Your payment has been received','Thank you for payment order '.$invoice,'','',$token['firebase_token'],'/tabs/tab3',NULL);
                    }
                }
                $orderDetail = $this->orderDetail($invoice);
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
                $this->mailServer->InvoicePaymentSuccessCusttomer($details);
                $chat = "Hallo, ".$orderDetail['partner_detail']['name']." , mau info Ada bookingan masuk dari PAWLY APP:\n\nNama : ".$orderDetail['user_detail']['nickname']."\nBooking Service : ".$orderDetail['type']." - ".$orderDetail['service']."\nBooking Code : ".$orderDetail['order_id']."\n\nMohon dibantu proses ya kak, Terimakasih 🙏😊";

                $wa = $this->whatsapp->sendWaText($details['partnerDetail']['phone_number'], $chat);
                $this->socket->update_order($invoice, $orderDetail['results']['status'],$orderDetail['results']['users_ids'],$orderDetail['results']['partner_user_id']);

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
            $query->update([
                'status' => 'ORDER_COMPLATE',
                'updated_at' => Carbon::now()
            ]);
        }
    }

       
    public function createVcLink(request $request){
        $query2 = orderservice::where('order_id','like',$request->order_id);
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
        $query2->update([
            'status' => 'ON_PROCESS',
            'updated_at' => Carbon::now()
        ]);
        if($query == 1){
            return response()->JSON([
                'status' => 'success',
                'url'   => $saveddata['hostRoomUrl'],
                'msg' => ''
            ]);
        }else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'Failed create room'
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
        
        if($service==NULL){
            $service = ['grooming','vaksin','umum'];
        }
        if($result['status'] == 200){ 
            $data = orderservice::join('users','orderservices.users_ids','=','users.id')
            ->join('order_details','orderservices.order_id','=','order_details.order_id')
            ->select('users.*','orderservices.*')
            ->where('users.email','like','%'.$request->email.'%')
            ->where('users.nickname','like','%'.$request->name.'%')
            ->where('partner_user_id','like', $result['body']['user_id'])
            ->where('orderservices.order_id','like','%'.$orderId.'%')
            ->where('type','like','%'.$type.'%')
            ->wherein('order_details.service_name','like',$service)
            ->where('orderservices.status','like','%'.$status.'%')
            ->where('booking_date','like','%'.$date.'%')
            ->orderBy('booking_date','ASC');
            // $data = orderservice::join('users','orderservices.users_ids','=','users.id')->select('users.*','orderservices.*')->where('users.email','like','%'.$request->email.'%')->wheredate('created_at',$request->order_date)->where('users.nickname','like','%'.$request->name.'%')->where('partner_user_id','like', $result['body']['user_id'])->where('order_id','like','%'.$orderId.'%')->where('type','like','%'.$type.'%')->where('service','like','%'.$service.'%')->where('orderservices.status','like','%'.$status.'%')->where('booking_date','like','%'.$date.'%')->orderBy('booking_date','ASC');
            $result=[];
            $resu = NULL;

            if($request->order_date==NULL){
                $resu = $data->limit($limit)->offset($page)->get();
            } else{
                $resu = $data->wheredate('created_at',$request->order_date)->limit($limit)->offset($page)->get();
            }
                
            foreach($resu as $arr){
                if($arr['coupon_name']==NULL){
                    $payment_allowed = 'a:2:{i:0;s:4:"dana";i:1;s:3:"ovo";}';
                } else{
                    $payment_allowed = couponservice::where('coupon_name',$data->value('coupon_name'))->value('allowed_payment');
                }
                $method = array(
                    'id' => $arr['id'],
                    'order_id'=>$arr['order_id'],
                    'service'=>order_detail::where('order_id','like','%'.$arr['order_id'].'%')->select('service_name')->get(),
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
                    'email'=>User::where('id',$arr['users_ids'])->value('email'),
                    'phone_number'=>User::where('id',$arr['users_ids'])->value('phone_number'),
                    'gender'=>User::where('id',$arr['users_ids'])->value('gender'),
                    'profile_picture'=>User::where('id',$arr['users_ids'])->value('profile_picture'),
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
    
        if($service==NULL){
            $service = ['vaksin','grooming','umum'];
        }
        if($result['status'] == 200){ 
            $data = orderservice::where('partner_user_id','like', $result['body']['user_id'])
            ->where('orderservices.order_id','like','%'.$orderId.'%')
            ->where('type','like','%'.$type.'%')
            ->wherein('service','like',$service)
            ->where('status','like','%'.$status.'%')
            ->orderBy('booking_date','ASC');
            $result=[];
                
            foreach($data->limit($limit)->offset($page)->get() as $arr){
                $method = array(
                    'id' => $arr['id'],
                    'order_id'=>$arr['order_id'],
                    'service'=>order_detail::where('order_id','like','%'.$arr['order_id'].'%')->select('service_name')->get(),
                    'service_id'=>$arr['service_id'],
                    'pet_id'=>$arr['pet_id'],
                    'type'=>$arr['type'],
                    'status'=>$arr['status'],
                    'users_ids'=>$arr['users_ids'],
                    'user_name'=>User::where('id',$arr['users_ids'])->value('nickname'),
                    'user_image'=>User::where('id',$arr['users_ids'])->value('profile_picture'),
                    'partner_user_id'=>$arr['partner_user_id']
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
        $order_id = $request->order_id;

        $order = orderservice::where('order_id',$order_id)->get();

        if($order->value('status')=='BOOKING RESERVED'){
            $update = orderservice::where('order_id',$order_id)->update([
                'cancelled_at' => carbon::now()->timestamp,
                'cancelled_reason' => 'doctor reject order',
                'status' => 'BOOKING_CANCEL',
                'refund_at' => carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $token_fb = $this->fb_token->userFirebaseToken( orderservice::where('order_id','like',$order_id)->value('users_ids'),'Consumer App');
            foreach( $token_fb as $token){
                if($token['firebase_token'] != NULL){
                    $notification = $this->mobile_banner->send_notif('Your Order '.$order_id.' Has Been Cancelled','Your Money will be refuned max 1x24','','',$token['firebase_token'],'/tabs/tab3',NULL);
                }
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
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'You can not cancel this order'
            ]);
            
        }
    }

    public function acceptOrder(request $request){
        $order_id = $request->order_id;

        $order = orderservice::where('order_id',$order_id);

        if($order->value('status')=='BOOKING RESERVED'){
            $update = orderservice::where('order_id',$order_id)->update([
                'status' => 'ON_PROCESS',
                'updated_at' => Carbon::now()
            ]);
            $status = 'success';
            $msg = null;
            $token_fb = $this->fb_token->userFirebaseToken( orderservice::where('order_id','like',$order_id)->value('users_ids'),'Consumer App');
            foreach( $token_fb as $token){
                if($token['firebase_token'] != NULL){
                    $notification = $this->mobile_banner->send_notif('Your order is now in process','Order '.$order_id.' now on process','','',$token['firebase_token'],"/order-detail"."/".$order_id,NULL);
                }
            }
        } else{
            $status = 'error';
            $msg = 'Failed accept booking';
        }

        return response()->JSON([
            'status' => $status,
            'msg' => $msg
        ]);
    }

    public function userorderlist(request $request){
        $mode = $request->mode;

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
            if($mode=='PAST'){
                $status = ['ORDER_COMPLATE'];
            } else if($mode=='UPCOMING'){
               $status = ['PENDING_PAYMENT','BOOKING RESERVED'];
            }

            $data = orderservice::join('users','orderservices.users_ids','=','users.id')
            ->join('order_details','orderservices.order_id','=','order_details.order_id')
            ->select('users.*','orderservices.*')
            ->where('users.id','like', $result['body']['user_id'])
            ->where('orderservices.type','not like','wallet')
            ->wherein('orderservices.status',$status)
            ->orderbyraw(
                "case
                when order_details.service_name = 'vidcall' then 1
                when order_details.service_name = 'chat' then 2
                when order_details.service_name = 'offline' then 3
                end asc"
            );
            
            $result=[];
            $jmlhreview = 0;
            
            foreach($data->limit($limit)->offset($page)->get() as $arr){
                $ratings = ratings::where('doctors_ids',$arr['service_id']);
                if($ratings->count()==0){
                    $avgratings = 0;
                    $jmlhreview = 0;
                } else{
                    $avgratings = round($ratings->avg('ratings'));
                    $jmlhreview = ratings::where('doctors_ids',$arr['service_id'])->count();
                }
                $userDetail = doctor::where('id',$arr['service_id']);
                if($arr['type'] == 'doctor'){
                    $partnerDetail=[
                        'users_ids'=>$arr['partner_user_id'],
                        'partner_name'=>$userDetail->value('doctor_name'),
                        'profile_picture'=> $userDetail->value('profile_picture'),
                        'address'=> $userDetail->value('address'),
                        'rating' => $avgratings,
                        'total_review' => $jmlhreview
                    ];
                }else if($arr['type'] == 'clinic'){
                    $partnerDetail=[
                        'users_ids'=>$arr['partner_user_id'],
                        'partner_name'=>$userDetail->value('doctor_name'),
                        'profile_picture'=> $userDetail->value('profile_picture'),
                        'address'=> $userDetail->value('address'),
                        'rating' => $avgratings,
                        'total_review' => $jmlhreview
                    ];
                }else{
                    $partnerDetail=[];
                }
                $method = array(
                    'id' => $arr['id'],
                    'order_id'=>$arr['order_id'],
                    'service'=>order_detail::where('order_id','like','%'.$arr['order_id'].'%')->select('service_name')->get(),
                    'service_id'=>$arr['service_id'],
                    'type'=>$arr['type'],
                    'pet_id'=>$arr['pet_id'],
                    'status'=>$arr['status'],
                    'booking_time'=>$arr['booking_date'],
                    'partnerDetail'=> $partnerDetail,
                    'created_at'=>$arr['created_at'],
                    'updated_at'=>$arr['updated_at']
                );
                array_push($result,$method);
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

    public function saasorderlist(request $request){
        $mode = $request->mode;
        $bookingst = Carbon::create($request->startbookingdate)->todatetimestring();
        $bookinged = Carbon::create($request->endbookingdate)->addhour(23)->addminutes(59)->addseconds(59)->todatetimestring();
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
            if($mode=='PAST'){
                $status = ['ORDER_COMPLATE'];
            } else if($mode=='UPCOMING'){
               $status = ['PENDING_PAYMENT','BOOKING RESERVED'];
            }

            $data = orderservice::join('users','orderservices.users_ids','=','users.id')
            ->select('users.*','orderservices.*')
            ->where('users.id','like', $result['body']['user_id'])
            ->where('orderservices.type','not like','wallet')
            ->wherein('orderservices.status',$status)
            ->orderbyraw(
                "case
                when orderservices.service = 'vidcall' then 1
                when orderservices.service = 'chat' then 2
                when orderservices.service = 'offline' then 3
                end asc"
            );
            
            $result=[];

            foreach($data->limit($limit)->offset($page)->get() as $arr){
                $method = array(
                    'id' => $arr['id'],
                    'order_id'=>$arr['order_id'],
                    'service'=>order_detail::where('order_id','like','%'.$arr['order_id'].'%')->select('service_name')->get(),
                    'service_id'=>$arr['service_id'],
                    'pet_id'=>$arr['pet_id'],
                    'status'=>$arr['status'],
                    'users_ids'=>$arr['users_ids'],
                    'Booking_date'=>$arr['booking_date'],
                    'user_name'=>User::where('id',$arr['users_ids'])->value('nickname'),
                    'email'=>User::where('id',$arr['users_ids'])->value('email'),
                    'phone_number'=>User::where('id',$arr['users_ids'])->value('phone_number'),
                    'gender'=>User::where('id',$arr['users_ids'])->value('gender'),
                    'profile_picture'=>User::where('id',$arr['users_ids'])->value('profile_picture'),
                    'partner_user_id'=>$arr['partner_user_id'],
                    'created_at'=>$arr['created_at'],
                    'updated_at'=>$arr['updated_at']
                );
                array_push($result,$method);
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
}