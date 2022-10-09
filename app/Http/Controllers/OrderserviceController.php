<?php

namespace App\Http\Controllers;

use App\Models\orderservice;
use Illuminate\Http\Request;
use App\Http\Controllers\CouponserviceController;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;
use App\Http\Controllers\JWTValidator;

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
            $userid = $result['body']['user_id'];
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if($coupon_name==NULL){
            $total_price = $price;
            $discount = 0;
            $subtotal = $total_price-$discount;
            $query = orderservice::insertGetId([
                'service' => $service,
                'service_id' => $service_id,
                'type' => $type,
                'users_ids' => $userid,
                'status' => 'pending',
                'total' => $total_price,
                'diskon' => $discount,
                'subtotal' => $subtotal
            ]);
            $insertorderid = orderservice::where('id',$query)->update([
                'order_id' => substr(str_shuffle(str_repeat($pool, 5)), 0, 8).$query
            ]);

            if($insertorderid==1){
                return response()->JSON([
                    'status' => 'success',
                    'results' => orderservice::where('id',$query)->get()
                ]);
            } else{
                return response()->JSON([
                    'status' => 'error'
                ]);
            }
            
        } else{
            $coupons_respond = $this->coupons->coupon_service($coupon_name,$userid,$service,$price);
            if($coupons_respond['result']=='success'){
                $total_price = $price;
                $discount = $coupons_respond['value'];
                $subtotal = $total_price-$discount;
                $query = orderservice::insertGetId([
                'service' => $service,
                'service_id' => $service_id,
                'type' => $type,
                'status' => 'pending',
                'users_ids' => $userid,
                'coupon_name' => $coupon_name,
                'total' => $total_price,
                'diskon' => $discount,
                'subtotal' => $subtotal
            ]);
            $insertorderid = orderservice::where('id',$query)->update([
                'order_id' => substr(str_shuffle(str_repeat($pool, 5)), 0, 8).$query
            ]);

            if($insertorderid==1){
                return response()->JSON([
                    'status' => 'success',
                    'results' => orderservice::where('id',$query)->get()
                ]);
            } else{
                return response()->JSON([
                    'status' => 'error'
                ]);
            }
            } else{
                
                return response()->JSON([
                    'status' => 'error'
                ]);
            
            }
        }
    } else{
        return response()->JSON([
            'status' => 'error'
        ]);
    }
    }
        
}
