<?php

namespace App\Http\Controllers;

use App\Models\orderservice;
use Illuminate\Http\Request;
use App\Http\Controllers\CouponserviceController;

class OrderserviceController extends Controller
{
    public function order_service($service,$price,$coupon_name,$service_id,$type){
        if($coupon_name==NULL){
            $total_price = $price;
            $discount = 0;
            $subtotal = $total_price-$discount;
            $query = orderservice::insert([
                'service' => $service,
                'service_id' => $service_id,
                'type' => $type,
                'status' => 'pending',
                'total' => $total_price,
                'diskon' => $discount,
                'subtotal' => $subtotal
            ]);
            if($query==1){
                return response()->JSON([
                    'status' => 'success',
                    'results' => $query
                ]);
            } else{
                return response()->JSON([
                    'status' => 'error'
                ]);
            }
            
        } else{
            
        }
    }
}
