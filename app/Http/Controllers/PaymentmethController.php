<?php

namespace App\Http\Controllers;

use App\Models\paymentmeth;
use Illuminate\Http\Request;
use stdClass;

class PaymentmethController extends Controller
{
    //$service,$payment
    public function payment_method(Request $request){
        $arr = array(
            array('payment_method' => 'Ovo',
            'image_url'=>'http://',
            'description'=>'make your life more simple'),

            array('payment_method' => 'Dana',
            'image_url'=>'http://',
            'description'=>'make your life more simple'),

            array('payment_method' => 'GoPay',
            'image_url'=>'http://',
            'description'=>'make your life more simple'),

        );
      
        $payment_allowed = new stdClass;

        if($request->payment==NULL){
            $allowed_payment = unserialize(paymentmeth::where('service',$request->service)->value('allowed_payment'));
            $payment_allowed = array();
            
            foreach($arr as $arr){
                foreach($allowed_payment as $allow){
                    if($arr['payment_method'] == $allow){
                        $method = array(
                            'payment_method' => $arr['payment_method'],
                            'image_url'=>$arr['image_url'],
                            'description'=>$arr['description'],
                            'active'=>true
                        );
                    }else{
                        $method = array(
                            'payment_method' => $arr['payment_method'],
                            'image_url'=>$arr['image_url'],
                            'description'=>$arr['description'],
                            'active'=>false
                        );
                    }
                   
                }
                array_push($payment_allowed, $method);
            }
            return response()->JSON([
                'status' => 'success',
                'results' => $payment_allowed
            ]);
        } else{
            $allowed_payment = json_decode($payment,true);
            
            foreach($allowed_payment as $allow){
                if(array_key_exists($allow, $arr)){
                    $payment_allowed->payment = $allow;
                    $payment_allowed->access = 'true';
                } else{
                    $payment_allowed->payment = $allow;
                    $payment_allowed->access = 'false';
                }
            }
            return response()->JSON([
                'status' => 'success',
                'results' => $payment_allowed
            ]);
        }

    }   
}
