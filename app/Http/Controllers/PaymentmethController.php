<?php

namespace App\Http\Controllers;

use App\Models\paymentmeth;
use Illuminate\Http\Request;
use stdClass;
use App\Models\wallet;
use App\Http\Controllers\JWTValidator;
use App\Models\orderservice;

class PaymentmethController extends Controller
{
    protected $JWTValidator;
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }
    

    //$service,$payment
    public function payment_method(Request $request){
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $wallet = wallet::where('users_ids', $result['body']['user_id']);
        $ammount = $wallet->sum('debit') - $wallet->sum('credit');
        $total_transaction = orderservice::where('order_id','like',$request->order_id)->value('subtotal');
       
        $arr = array(
            array('payment_method' => 'Wallet',
            'total_ammount'=>$ammount,
            'image_url'=>'http://',
            'description'=>'make your life more simple'),

            array('payment_method' => 'BCA Transfer',
            'total_ammount'=>null,
            'image_url'=>'http://',
            'description'=>'make your life more simple'),

            array('payment_method' => 'Dana',
            'total_ammount'=>null,
            'image_url'=>'http://',
            'description'=>'make your life more simple'),

            array('payment_method' => 'Ovo',
            'total_ammount'=>null,
            'image_url'=>'http://',
            'description'=>'make your life more simple'),
           
            array('payment_method' => 'GoPay',
            'total_ammount'=>null,
            'image_url'=>'http://',
            'description'=>'make your life more simple'),

        );
      
        $payment_allowed = new stdClass;
        if($result['status'] == 200){
            if($request->payment==NULL){
                $allowed_payment = unserialize(paymentmeth::where('service',$request->service)->value('allowed_payment'));
                $payment_allowed = array();
                    foreach($arr as $arr){ //statis
                        foreach($allowed_payment as $allow){ // dari db
                  
    
                        if($arr['payment_method'] == $allow){
                           
                            $method = array(
                                'payment_method' => $arr['payment_method'],
                                'total_ammount'=>$arr['total_ammount'],
                                'image_url'=>$arr['image_url'],
                                'description'=>$arr['description'],
                                'active'=>true
                            );
                            break;
                        }else{
                          
                            $method = array(
                                'payment_method' => $arr['payment_method'],
                                'total_ammount'=>$arr['total_ammount'],
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
                $allowed_payment = unserialize($request->payment);
                
                $payment_allowed = array();
                foreach($arr as $arr){ //statis
                    foreach($allowed_payment as $allow){ // dari db
              

                    if($arr['payment_method'] == $allow){
                        if($arr['payment_method'] == "Wallet" && $total_transaction > $ammount){
                            $active = false;
                            $desc = "Sorry, your balance is less than the total transaction";
                        }else{
                            $active = true;
                            $desc = $arr['description'];
                        }
                        $method = array(
                            'payment_method' => $arr['payment_method'],
                            'total_ammount'=>$arr['total_ammount'],
                            'image_url'=>$arr['image_url'],
                            'description'=> $desc,
                            'active'=>$active
                        );
                        break;
                    }else{
                      
                        $method = array(
                            'payment_method' => $arr['payment_method'],
                            'total_ammount'=>$arr['total_ammount'],
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
            }
        }else{
            return array(
                $result
            );
        }

       

    }   
}
