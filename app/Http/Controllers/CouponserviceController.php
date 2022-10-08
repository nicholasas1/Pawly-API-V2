<?php

namespace App\Http\Controllers;

use App\Models\couponservice;
use Illuminate\Http\Request;
use App\Models\couponusages;
use Carbon\Carbon;

class CouponserviceController extends Controller
{

    public function coupon_service(request $request){
        $coupon_name = $request->name;
        $user_id = $request->id;
        $service = $request->service;
        $price = $request->price;
        $coupon = couponservice::where('coupon_name',$coupon_name)->where('coupon_service',$service);

        if($coupon->count()==0){
            return response()->JSON([
                'status' => 'error',
                'validate' => 'no_coupon',
                'value' => '0'
            ]);
        } else {
            $totalusage = $usages = couponusages::where('coupon_name',$coupon_name);
            if($coupon->value('coupon_rule')=='once_per_day'){
                $date = Carbon::today()->toDateString();
                $usages = couponusages::where('coupon_name',$coupon_name)->where('user_id',$user_id)->where('date',$date);

                if($totalusage->count()>$coupon->value('max_usage')||$usages->count()>0){
                    return response()->JSON([
                        'status' => 'error',
                        'validate' => 'max_usage_passed',
                        'value' => '0'
                    ]);
                } else {
                    if($price<$coupon->value('min_price')||$price>$coupon->value('max_price')){
                        return response()->JSON([
                            'status' => 'error',
                            'validate' => 'price_invalid',
                            'value' => '0'
                        ]);
                    } else{
                        if($coupon->value('coupon_type')=='percent'){
                            $totaldiscount = $price*$coupon->value('coupon_value')/100;
                        } else{
                            $totaldiscount = $coupon->value('coupon_value');
                        }
                        return response()->JSON([
                            'status' => 'success',
                            'validate' => 'coupon_avaiable',
                            'value' => $totaldiscount,
                            'allowed_payment' => $coupon->value('allowed_payment')
                        ]);
                    }
                }
            } else if($coupon->value('coupon_rule')=='once_per_account'){
                $date = Carbon::today()->toDateString();
                $usages = couponusages::where('coupon_name',$coupon_name)->where('user_id',$user_id);

                if($totalusage->count()>$coupon->value('max_usage')||$usages->count()>0){
                    return response()->JSON([
                        'status' => 'error',
                        'validate' => 'max_usage_passed',
                        'value' => '0'
                    ]);
                } else {
                    if($price<$coupon->value('min_price')||$price>$coupon->value('max_price')){
                        return response()->JSON([
                            'status' => 'error',
                            'validate' => 'price_invalid',
                            'value' => '0'
                        ]);
                    } else{
                        if($coupon->value('coupon_type')=='percent'){
                            $totaldiscount = $price*$coupon->value('coupon_value')/100;
                        } else{
                            $totaldiscount = $coupon->value('coupon_value');
                        }
                        return response()->JSON([
                            'status' => 'success',
                            'validate' => 'coupon_avaiable',
                            'value' => $totaldiscount,
                            'allowed_payment' => $coupon->value('allowed_payment')
                        ]);
                    }
                }
            } else if($coupon->value('coupon_rule')=='anytime'){
                if($totalusage->count()>$coupon->value('max_usage')){
                    return response()->JSON([
                        'status' => 'error',
                        'validate' => 'max_usage_passed',
                        'value' => '0'
                    ]);
                } else{
                    if($price<$coupon->value('min_price')||$price>$coupon->value('max_price')){
                        return response()->JSON([
                            'status' => 'error',
                            'validate' => 'price_invalid',
                            'value' => '0'
                        ]);
                    } else{
                        if($coupon->value('coupon_type')=='percent'){
                            $totaldiscount = $price*$coupon->value('coupon_value')/100;
                        } else{
                            $totaldiscount = $coupon->value('coupon_value');
                        }
                        return response()->JSON([
                            'status' => 'success',
                            'validate' => 'coupon_avaiable',
                            'value' => $totaldiscount,
                            'allowed_payment' => $coupon->value('allowed_payment')
                       ]);            
                    }
                }
            }
        }
    }
}
