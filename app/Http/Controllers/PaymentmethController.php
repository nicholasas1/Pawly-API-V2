<?php

namespace App\Http\Controllers;

use App\Models\paymentmeth;
use Illuminate\Http\Request;
use stdClass;

class PaymentmethController extends Controller
{
    public function payment_method($service,$payment){
        $arr = ['ovo','gopay','wallet'];
        $payment_allowed = new stdClass;

        if($payment==NULL){
            $allowed_payment = paymentmeth::where('service',$service)->select('allowed_payment')->get();
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
