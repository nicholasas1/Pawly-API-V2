<?php

namespace App\Http\Controllers;

use App\Models\couponservice;
use Illuminate\Http\Request;
use App\Models\couponusages;
use App\Http\Controllers\JWTValidator;
use Carbon\Carbon;

class CouponserviceController extends Controller
{
    protected $JWTValidator;
    public function __construct(JWTValidator $jWTValidator)
    {
        $this->JWTValidator = $jWTValidator;
    }

    public function validate_coupon(request $request){
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $response =  $this->coupon_service($request->coupon_name, $result['body']['user_id'],$request->service,$request->price);

        if($result['status'] == 200){
            return $response;
        }else{
            return $result;
        }
    }

    public function coupon_service($coupon_name,$user_id,$service,$price){
        $coupon = couponservice::where('coupon_name',$coupon_name)->where('coupon_service',$service);

        if($coupon->count()==0){
            $response = array(
                'status' => 'error',
                'msg' => 'no_coupon',
                'value' => '0'
            );
        } else {
            $totalusage = $usages = couponusages::where('coupon_name',$coupon_name);
            if($coupon->value('coupon_rule')=='once_per_day'){
                $date = Carbon::today()->toDateString();
                $timestamp = Carbon::parse($date)->timestamp;
                $coupon_end_timestamp = Carbon::parse($coupon->value('end_date_time'))->timestamp;
                $coupon_start_timestamp = Carbon::parse($coupon->value('start_date_time'))->timestamp;
                if($date>$coupon_end_timestamp||$date<$coupon_start_timestamp){
                    return response()->JSON([
                        'status' => 'error',
                        'msg' => 'exceed the following day',
                        'value' => '0'
                    ]);
                } else{
                $usages = couponusages::where('coupon_name',$coupon_name)->where('user_id',$user_id)->where('date',$date);

                if($totalusage->count()>$coupon->value('max_usage')||$usages->count()>0){
                    $response = array(
                        'status' => 'error',
                        'msg' => 'max_usage_passed',
                        'value' => '0'
                    );
                } else {
                    if($price<$coupon->value('min_price')||$price>$coupon->value('max_price')){
                        $response = array(
                            'status' => 'error',
                            'msg' => 'price_invalid',
                            'value' => '0'
                        );
                    } else{
                        if($coupon->value('coupon_type')=='percent'){
                            $totaldiscount = $price*$coupon->value('coupon_value')/100;
                        } else{
                            $totaldiscount = $coupon->value('coupon_value');
                        }
                        $response = array(
                            'status' => 'success',
                            'msg' => 'coupon_avaiable',
                            'value' => $totaldiscount,
                            'allowed_payment' => $coupon->value('allowed_payment')
                        );
                    }
                }
                }
                
            } else if($coupon->value('coupon_rule')=='once_per_account'){
                $date = Carbon::today()->toDateString();
                $timestamp = Carbon::parse($date)->timestamp;
                $coupon_end_timestamp = Carbon::parse($coupon->value('end_date_time'))->timestamp;
                $coupon_start_timestamp = Carbon::parse($coupon->value('start_date_time'))->timestamp;
                if($timestamp>$coupon_end_timestamp||$timestamp<$coupon_start_timestamp){
                    return response()->JSON([
                        'status' => 'error',
                        'msg' => 'exceed the following day',
                        'value' => '0'
                    ]);
                } else{
                    $usages = couponusages::where('coupon_name',$coupon_name)->where('user_id',$user_id);

                    if($totalusage->count()>$coupon->value('max_usage')||$usages->count()>0){
                        $response = array(
                            'status' => 'error',
                            'msg' => 'max_usage_passed',
                            'value' => '0'
                        );
                    } else {
                        if($price<$coupon->value('min_price')||$price>$coupon->value('max_price')){
                            $response = array(
                                'status' => 'error',
                                'msg' => 'price_invalid',
                                'value' => '0'
                            );
                        } else{
                            if($coupon->value('coupon_type')=='percent'){
                                $totaldiscount = $price*$coupon->value('coupon_value')/100;
                            } else{
                                $totaldiscount = $coupon->value('coupon_value');
                            }
                            $response = array(
                                'status' => 'success',
                                'msg' => 'coupon_avaiable',
                                'value' => $totaldiscount,
                                'allowed_payment' => $coupon->value('allowed_payment')
                            );
                        }
                    }
                }
                
            } else if($coupon->value('coupon_rule')=='anytime'){
                $date = $date = Carbon::today()->toDateString();
                $timestamp = Carbon::parse($date)->timestamp;
                $coupon_end_timestamp = Carbon::parse($coupon->value('end_date_time'))->timestamp;
                $coupon_start_timestamp = Carbon::parse($coupon->value('start_date_time'))->timestamp;
                if($date>$coupon_end_timestamp||$date<$coupon_start_timestamp){
                    return response()->JSON([
                        'status' => 'error',
                        'msg' => 'exceed the following day',
                        'value' => '0'
                    ]);
                } else{
                    if($totalusage->count()>$coupon->value('max_usage')){
                        $response = array(
                            'status' => 'error',
                            'msg' => 'max_usage_passed',
                            'value' => '0'
                        );
                    } else{
                        if($price<$coupon->value('min_price')||$price>$coupon->value('max_price')){
                            $response = array(
                                'status' => 'error',
                                'msg' => 'price_invalid',
                                'value' => '0'
                            );
                        } else{
                            if($coupon->value('coupon_type')=='percent'){
                                $totaldiscount = $price*$coupon->value('coupon_value')/100;
                            } else{
                                $totaldiscount = $coupon->value('coupon_value');
                            }
                            $response = array(
                                'status' => 'success',
                                'msg' => 'coupon_avaiable',
                                'value' => $totaldiscount,
                                'allowed_payment' => $coupon->value('allowed_payment')
                            );
                        }
                    }
                }
                
            }
        }
        return $response;
    }

    public function create_coupon(request $request){
        $data = json_decode($request->allowed_payment);
        $serilize = serialize($data);
        $query = couponservice::insert([
            'coupon_name' => $request->coupon_name,
            'coupon_type' => $request->coupon_type,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'coupon_service' => $request->coupon_service,
            'allowed_payment' => $serilize,
            'coupon_rule' => $request->coupon_rule,
            'coupon_value' => $request->coupon_value,
            'max_usage' => $request->max_usage,
            'description' => $request->description,
            'term_link' => $request->link,
            'start_date_time' =>  $request->start_date_time,
            'end_date_time' =>  $request->end_date_time
        ]);


        if($query==1){
            return response()->JSON([
                'status' => 'success',
                'msg' => ''
            ]);
        } 
        else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'Failed Created Coupon'
            ]);
            
        }
    }

    public function delete_coupon(request $request){
        $query = couponservice::where('coupon_name',$request->query('coupon_name'))->delete();
        
        if($query==1){
            return response()->JSON([
                'status' => 'success',
                'msg' => ''
            ]);
        } 
        else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'Failed Delete Coupon'
            ]);
            
        }
    }

    public function update_coupon(request $request){
        $query = couponservice::where('coupon_name',$request->query('coupon_name'))->update(
                    [
                        'coupon_type' => $request->coupon_type,
                        'min_price' => $request->min_price,
                        'max_price' => $request->max_price,
                        'coupon_service' => $request->coupon_service,
                        'allowed_payment' => $request->allowed_payment,
                        'coupon_rule' => $request->coupon_rule,
                        'coupon_value' => $request->coupon_value,
                        'max_usage' => $request->max_usage,
                        'description' => $request->description,
                        'term_link' => $request->link,
                        'start_date_time' =>  $request->start_date_time,
                        'end_date_time' =>  $request->end_date_time
                    ]
                );
        
        if($query==1){
            return response()->JSON([
                'status' => 'success',
                'msg' => ''
            ]);
        } 
        else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'Failed Delete Coupon'
            ]);
            
        }
    }

    public function getlist(request $request)
    {
        $name = $request->name;
        if($request->sort == 'name_asc'){
            $order = "coupon_name";
            $order_val = "ASC";
        }else if($request->sort == 'name_dsc'){
            $order = "coupon_name";
            $order_val = "DESC";
        }else{
            $order = "coupon_name";
            $order_val = "DESC";
        }

        $data = couponservice::where('coupon_name','like','%'.$name.'%')->orderBy($order,$order_val);

        return response()->json([
            'status'=>'success', 
            'total_data'=>$data->count(), 
            'results'=>$data->get()
        ]);
       
        
    }

    public function getDetail(request $request)
    {
        $name = $request->name;
        
        $data = couponservice::where('coupon_name','like','%'.$name.'%');

        $arr = [
            'coupon_name' => $data->value('coupon_name'),
            'coupon_type' => $data->value('coupon_type'),
            'min_price' => $data->value('min_price'),
            'max_price' => $data->value('max_price'),
            'coupon_service' => $data->value('coupon_service'),
            'allowed_payment' => $data->value('allowed_payment'),
            'coupon_rule' => $data->value('coupon_rule'),
            'coupon_value' => $data->value('coupon_value'),
            'max_usage' => $data->value('max_usage'),
            'description' => $data->value('description'),
            'term_link' => $data->value('term_link'),
            'start_date_time' => $data->value('start_date_time'),
            'end_date_time' => $data->value('end_date_time'),
        ]; 

        return response()->json([
            'status'=>'success',
            'results'=>$arr
        ]);
       
        
    }

}
